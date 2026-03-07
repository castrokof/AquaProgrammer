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

    // Validaciones iniciales...
    if (!in_array($periodo->estado, ['PLANIFICADO', 'ACTIVO'])) {
        return response()->json([
            'ok' => false,
            'mensaje' => 'El período debe estar en estado PLANIFICADO o ACTIVO.',
        ], 422);
    }

    $yaExisten = Ordenesmtl::where('periodo_lectura_id', $periodo->id)->count();
    if ($yaExisten > 0) {
        return response()->json([
            'ok' => false,
            'mensaje' => "Este período ya tiene {$yaExisten} órdenes generadas.",
        ], 422);
    }

        $clientes = Cliente::with('estrato')
            ->where('estado', 'ACTIVO')
            ->orderBy('sector')
            ->orderBy('suscriptor')
            ->get();

    if ($clientes->isEmpty()) {
        return response()->json(['ok' => false, 'mensaje' => 'No hay clientes activos.'], 422);
    }

    // 👇 Obtener el siguiente ordenescu_id si NO es auto_increment
     $lastId = DB::table('ordenescu')->max('ordenescu_id') ?? 0;
     $nextOrdenescuId = $lastId + 1;

    $conMedidor = 0;
    $sinMedidor = 0;
    $facturacion = new FacturacionService();

    DB::beginTransaction();
    try {
        foreach ($clientes as $cliente) {
            if ($cliente->tiene_medidor) {
                
                // 👇 1. Obtener LA y Promedio del histórico
                $lecturaPrev = $this->obtenerLecturaAnterior($cliente->suscriptor, $periodo->codigo);

                // 👇 2. CREAR PRIMERO EN ENTRADA (para obtener el ID válido)
                $entrada = \App\Models\Admin\Entrada::create([
                    // Campos obligatorios de tu tabla 'entrada' (ajusta según DESCRIBE entrada)
                    'Ciclo'           => $periodo->ciclo,
                    'Suscriptor'      => $cliente->suscriptor,
                    'Periodo'         => $periodo->codigo,
                    'Año'             => substr($periodo->codigo, 0, 4),
                    'Mes'             => substr($periodo->codigo, 4, 2),
                    'Nombre'          => $cliente->nombre,
                    'Apell'           => $cliente->apellido ?? 'APELLIDO',
                    'Ref_Medidor'     => $cliente->serie_medidor,
                    'Direccion'       => $cliente->direccion,
                    'Telefono'        => $cliente->telefono ?? '',
                    'Ruta'            => $cliente->ruta,
                    'id_Ruta'         => $cliente->id_ruta,
                    'idDivision'      => $cliente->id_ruta,
                    'uso'             => $cliente->tipo_uso,
                    'servicio'        => $cliente->servicios,
                    'consecutivoRuta' => $cliente->consecutivo,
                    'consecutivo_int' => $cliente->consecutivo,
                    'LA'              => $lecturaPrev['Lect_Actual'],
                    'Promedio'        => $lecturaPrev['Promedio'],
                    'Tope'            => 6, // valor por defecto
                    'estrato'         => $cliente->estrato ?? null,
                    'id_lectura'      => $nextOrdenescuId++, // se puede actualizar después
                    // 👇 Agrega aquí cualquier otro campo NOT NULL de tu tabla entrada
                ]);

                // 👇 3. AHORA SÍ: insertar en ordenescu usando el ID generado en entrada
                Ordenesmtl::create([
                    'ordenescu_id'       => $entrada->id,  // ✅ FK válida desde entrada
                    'Suscriptor'         => $cliente->suscriptor,
                    'Periodo'            => $periodo->codigo,
                    'Año'                => substr($periodo->codigo, 0, 4),
                    'Mes'                => substr($periodo->codigo, 4, 2),
                    'Ciclo'              => $periodo->ciclo,
                    'Nombre'             => $cliente->nombre,
                    'Apell'              => $cliente->apellido ?? 'APELLIDO',
                    'Direccion'          => $cliente->direccion,
                    'Telefono'           => $cliente->telefono ?? '',
                    'Ref_Medidor'        => $cliente->serie_medidor,
                    'Ruta'               => $cliente->ruta,
                    'LA'                 => $lecturaPrev['Lect_Actual'],
                    'Promedio'           => $lecturaPrev['Promedio'],
                    'Estado'             => 1, // CARGADO
                    'Estado_des'         => 'CARGADO',
                    'periodo_lectura_id' => $periodo->id,
                    'uso'                => $cliente->tipo_uso,
                    'servicio'           => $cliente->servicios,
                    'idDivision'         => $cliente->id_ruta,
                    'id_Ruta'            => $cliente->id_ruta,
                    'Consecutivo'        => $cliente->consecutivo,
                    'recorrido'        => $cliente->consecutivo,
                    'consecutivoRuta'    => $cliente->consecutivo, // 👈 Incrementa solo aquí
                    'fecha_de_ejecucion' => null, // 👈 Incrementa solo aquí
                    'Tope'            => 6,
                    'id_lectura'         => $entrada->id, // 👈 Mismo ID si es tu lógica
                    // 👇 Agrega aquí cualquier otro campo NOT NULL de ordenescu
                ]);

                $conMedidor++;
            } else {
                // Clientes sin medidor: facturación automática
                $consumo = max(1, (int) round($cliente->promedio_consumo));
                $datos = $facturacion->calcular($cliente, $consumo, $periodo);
                Factura::create($datos);
                $sinMedidor++;
            }
        }

        if ($periodo->estado === 'PLANIFICADO') {
            $periodo->update(['estado' => 'ACTIVO']);
        }

        DB::commit();

        return response()->json([
            'ok' => true,
            'con_medidor' => $conMedidor,
            'sin_medidor' => $sinMedidor,
            'mensaje' => "{$conMedidor} órdenes generadas · {$sinMedidor} facturados automáticamente.",
        ]);

    } catch (\Throwable $e) {
        DB::rollBack();
        \Log::error('Error en generarOrdenes: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'suscriptor' => $cliente->suscriptor ?? null
        ]);
        return response()->json([
            'ok' => false,
            'mensaje' => 'Error al generar órdenes: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Helper: Obtiene la lectura anterior y promedio de un cliente
 */
        private function obtenerLecturaAnterior($suscriptor, $periodoActual)
        {
            $ultima = Ordenesmtl::where('Suscriptor', $suscriptor)
                ->where('Estado', 4) // Ajusta: 2 = FINALIZADA
                ->where('Periodo', '<', $periodoActual)
                ->orderByDesc('Periodo')
                ->orderByDesc('Consecutivo')
                ->first();

            return $ultima 
                ? [
                    'Lect_Actual' => $ultima->Lect_Actual ?? $ultima->Promedio ?? 0,
                    'Promedio' => $ultima->Promedio ?? 0
                ]
                : [
                    'Lect_Actual' => 0,
                    'Promedio' => 0
                ];
        }
}
