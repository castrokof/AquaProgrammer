<?php

namespace App\Http\Controllers;

use App\Models\PeriodoLectura;
use App\Models\TarifaPeriodo;
use App\Models\Cliente;
use App\Models\ClienteHistoricoConsumo;
use App\Models\Factura;
use App\Models\Admin\Ordenesmtl;
use App\Services\FacturacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeriodoLecturaController extends Controller
{
    public function index()
    {
        $periodos = PeriodoLectura::with('tarifa')->orderBy('codigo', 'desc')->paginate(20);
        $tarifas  = TarifaPeriodo::where('activo', true)->orderBy('vigente_desde', 'desc')->get();
        return view('facturacion.periodos.index', compact('periodos', 'tarifas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo'               => 'required|string|size:6|unique:periodos_lectura,codigo',
            'nombre'               => 'required|string|max:80',
            'ciclo'                => 'required|integer|min:1',
            'tarifa_periodo_id'    => 'nullable|exists:tarifa_periodos,id',
            'fecha_inicio_lectura' => 'required|date',
            'fecha_fin_lectura'    => 'required|date|after_or_equal:fecha_inicio_lectura',
            'fecha_expedicion'     => 'required|date',
            'fecha_vencimiento'    => 'required|date|after_or_equal:fecha_expedicion',
            'fecha_corte'          => 'required|date|after_or_equal:fecha_vencimiento',
        ]);

        PeriodoLectura::create($request->all());

        return response()->json(['ok' => true, 'mensaje' => 'Período creado correctamente.']);
    }

    public function update(Request $request, $id)
    {
        $periodo = PeriodoLectura::findOrFail($id);

        $request->validate([
            'nombre'               => 'required|string|max:80',
            'ciclo'                => 'required|integer|min:1',
            'tarifa_periodo_id'    => 'nullable|exists:tarifa_periodos,id',
            'fecha_inicio_lectura' => 'required|date',
            'fecha_fin_lectura'    => 'required|date|after_or_equal:fecha_inicio_lectura',
            'fecha_expedicion'     => 'required|date',
            'fecha_vencimiento'    => 'required|date|after_or_equal:fecha_expedicion',
            'fecha_corte'          => 'required|date|after_or_equal:fecha_vencimiento',
            'observaciones'        => 'nullable|string',
        ]);

        $periodo->update($request->all());

        return response()->json(['ok' => true, 'mensaje' => 'Período actualizado.']);
    }

    /** Avanza el estado del período (flujo: PLANIFICADO → ACTIVO → LECTURA_CERRADA → FACTURADO → CERRADO) */
    public function cambiarEstado(Request $request, $id)
    {
        $periodo = PeriodoLectura::findOrFail($id);

        $flujo = [
            'PLANIFICADO'    => 'ACTIVO',
            'ACTIVO'         => 'LECTURA_CERRADA',
            'LECTURA_CERRADA'=> 'FACTURADO',
            'FACTURADO'      => 'CERRADO',
        ];

        $nuevoEstado = $request->input('estado') ?? ($flujo[$periodo->estado] ?? null);

        if (!$nuevoEstado) {
            return response()->json(['ok' => false, 'mensaje' => 'No hay estado siguiente.'], 422);
        }

        $periodo->update(['estado' => $nuevoEstado]);

        return response()->json(['ok' => true, 'nuevo_estado' => $nuevoEstado, 'mensaje' => "Estado cambiado a {$nuevoEstado}."]);
    }

    public function show($id)
    {
        $periodo = PeriodoLectura::with('tarifa')->findOrFail($id);
        return response()->json($periodo);
    }

    /**
     * Genera órdenes de lectura para todos los clientes activos del período.
     *
     * - Clientes CON medidor  → registro en ordenescu (Estado=1 PENDIENTE), en orden por sector (CU).
     * - Clientes SIN medidor  → factura automática usando el promedio de consumo.
     *
     * El período debe estar en PLANIFICADO o ACTIVO y sin órdenes previas.
     */
    public function generarOrdenes($id)
    {
        $periodo = PeriodoLectura::with('tarifa')->findOrFail($id);

        if (!in_array($periodo->estado, ['PLANIFICADO', 'ACTIVO'])) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'El período debe estar en estado PLANIFICADO o ACTIVO para generar órdenes.',
            ], 422);
        }

        $yaExisten = Ordenesmtl::where('periodo_lectura_id', $periodo->id)->count();
        if ($yaExisten > 0) {
            return response()->json([
                'ok'      => false,
                'mensaje' => "Este período ya tiene {$yaExisten} órdenes generadas. No se puede repetir.",
            ], 422);
        }

        $clientes = Cliente::with('estrato')
            ->where('estado', 'ACTIVO')
            ->orderBy('sector')
            ->orderBy('suscriptor')
            ->get();

        if ($clientes->isEmpty()) {
            return response()->json(['ok' => false, 'mensaje' => 'No hay clientes activos para generar órdenes.'], 422);
        }

        $conMedidor  = 0;
        $sinMedidor  = 0;
        $consecutivo = 1;
        $facturacion = new FacturacionService();

        DB::beginTransaction();
        try {
            foreach ($clientes as $cliente) {
                if ($cliente->tiene_medidor) {
                    Ordenesmtl::create([
                        'Suscriptor'         => $cliente->suscriptor,
                        'Periodo'            => $periodo->codigo,
                        'Año'                => substr($periodo->codigo, 0, 4),
                        'Mes'                => substr($periodo->codigo, 4, 2),
                        'Ciclo'              => $periodo->ciclo,
                        'Nombre'             => $cliente->nombre,
                        'Apell'              => $cliente->apellido,
                        'Direccion'          => $cliente->direccion,
                        'Telefono'           => $cliente->telefono,
                        'Ref_Medidor'        => $cliente->serie_medidor,
                        'Ruta'               => $cliente->sector,
                        'Consecutivo'        => $consecutivo++,
                        'Promedio'           => (int) round($cliente->promedio_consumo),
                        'Estado'             => 1, // PENDIENTE
                        'periodo_lectura_id' => $periodo->id,
                        'uso'                => $cliente->tipo_uso,
                        'servicio'           => $cliente->servicios,
                    ]);
                    $conMedidor++;
                } else {
                    // Sin medidor: facturar automáticamente con promedio
                    $consumo = max(1, (int) round($cliente->promedio_consumo));
                    $datos   = $facturacion->calcular($cliente, $consumo, $periodo);
                    Factura::create($datos);
                    $sinMedidor++;
                }
            }

            // Si el período estaba en PLANIFICADO, avanzar a ACTIVO
            if ($periodo->estado === 'PLANIFICADO') {
                $periodo->update(['estado' => 'ACTIVO']);
            }

            DB::commit();

            return response()->json([
                'ok'          => true,
                'con_medidor' => $conMedidor,
                'sin_medidor' => $sinMedidor,
                'mensaje'     => "{$conMedidor} órdenes de lectura generadas · {$sinMedidor} clientes sin medidor facturados automáticamente.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['ok' => false, 'mensaje' => 'Error al generar órdenes: ' . $e->getMessage()], 500);
        }
    }
}
