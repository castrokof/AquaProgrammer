<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Pago;
use App\Models\PeriodoLectura;
use App\Models\ClienteHistoricoConsumo;
use App\Models\ClienteOtrosCobro;
use App\Services\FacturacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade as PDF;

class FacturaController extends Controller
{
    protected FacturacionService $svc;

    public function __construct(FacturacionService $svc)
    {
        $this->svc = $svc;
    }

    // ── Listado ───────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $periodos = PeriodoLectura::orderBy('codigo', 'desc')->get(['id','codigo','nombre','estado']);

        $query = Factura::with('cliente')
            ->orderBy('periodo', 'desc')
            ->orderBy('numero_factura', 'desc');

        if ($p = $request->periodo) $query->where('periodo', $p);
        if ($s = $request->suscriptor) $query->where('suscriptor', 'like', "%{$s}%");
        if ($e = $request->estado) $query->where('estado', $e);

        $facturas = $query->paginate(25)->appends(request()->query());

        return view('facturacion.facturas.index', compact('facturas', 'periodos'));
    }

    // ── Generar (formulario + preview Ajax) ──────────────────────────────────

    public function generar()
    {
        $periodos = PeriodoLectura::whereIn('estado', ['ACTIVO','LECTURA_CERRADA','FACTURADO'])
            ->orderBy('codigo', 'desc')->get();

        return view('facturacion.facturas.generar', compact('periodos'));
    }

    /** Ajax: busca cliente por suscriptor y devuelve sus datos + últimos m³ */
    public function buscarCliente(Request $request)
    {
        $cliente = Cliente::with(['estrato', 'historicoConsumos' => function ($q) { $q->limit(6); }])
            ->where('suscriptor', $request->suscriptor)
            ->first();

        if (!$cliente) {
            return response()->json(['ok' => false, 'mensaje' => 'Suscriptor no encontrado.'], 404);
        }

        return response()->json([
            'ok'      => true,
            'cliente' => [
                'id'               => $cliente->id,
                'nombre'           => trim($cliente->nombre . ' ' . $cliente->apellido),
                'direccion'        => $cliente->direccion,
                'serie_medidor'    => $cliente->serie_medidor,
                'estrato'          => optional($cliente->estrato)->nombre ?? 'Sin estrato',
                'servicios'        => $cliente->servicios,
                'tipo_uso'         => $cliente->tipo_uso,
                'tiene_medidor'    => $cliente->tiene_medidor,
                'promedio_consumo' => $cliente->promedio_consumo,
                'estado'           => $cliente->estado,
            ],
            'historial' => $cliente->historicoConsumos->map(function ($h) { return [
                'periodo'   => $h->periodo,
                'consumo'   => $h->consumo_m3,
            ]; }),
        ]);
    }

    /** Ajax: calcula la previsualización de la factura sin guardar */
    public function preview(Request $request)
    {
        $request->validate([
            'cliente_id'        => 'required|exists:clientes,id',
            'periodo_lectura_id'=> 'required|exists:periodos_lectura,id',
            'consumo_m3'        => 'required|integer|min:0',
            'lectura_anterior'  => 'nullable|integer|min:0',
            'lectura_actual'    => 'nullable|integer|min:0',
        ]);

        $cliente = Cliente::with('estrato')->findOrFail($request->cliente_id);
        $periodo = PeriodoLectura::with('tarifa')->findOrFail($request->periodo_lectura_id);

        $calculo = $this->svc->calcular(
            $cliente,
            $request->consumo_m3,
            $periodo,
            $request->lectura_anterior,
            $request->lectura_actual
        );

        return response()->json(['ok' => true, 'calculo' => $calculo]);
    }

    /** Guarda la factura definitivamente */
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id'         => 'required|exists:clientes,id',
            'periodo_lectura_id' => 'required|exists:periodos_lectura,id',
            'consumo_m3'         => 'required|integer|min:0',
            'lectura_anterior'   => 'nullable|integer|min:0',
            'lectura_actual'     => 'nullable|integer|min:0',
            'observaciones'      => 'nullable|string',
        ]);

        // Verificar que no existe factura para este cliente en este período
        $existe = Factura::where('cliente_id', $request->cliente_id)
            ->where('periodo_lectura_id', $request->periodo_lectura_id)
            ->exists();

        if ($existe) {
            return response()->json(['ok' => false, 'mensaje' => 'Ya existe una factura para este cliente en el período seleccionado.'], 422);
        }

        $cliente = Cliente::with('estrato')->findOrFail($request->cliente_id);
        $periodo = PeriodoLectura::with('tarifa')->findOrFail($request->periodo_lectura_id);

        $calculo = $this->svc->calcular(
            $cliente, $request->consumo_m3, $periodo,
            $request->lectura_anterior, $request->lectura_actual
        );

        $calculo['observaciones'] = $request->observaciones;
        $calculo['usuario_id']    = auth()->id();
        $calculo['es_automatica'] = false; // generada manualmente

        $factura = Factura::create($calculo);

        // Registrar en historial de consumos (usa el consumo efectivo calculado,
        // que puede diferir del request cuando el cliente no tiene medidor)
        ClienteHistoricoConsumo::registrarYActualizarPromedio(
            $cliente->id, $cliente->suscriptor, $periodo->codigo,
            $calculo['consumo_m3'], $calculo['lectura_anterior'], $calculo['lectura_actual']
        );

        // Descontar cuotas de otros cobros
        ClienteOtrosCobro::where('cliente_id', $cliente->id)->activo()->each->pagarCuota();

        return response()->json(['ok' => true, 'factura_id' => $factura->id, 'mensaje' => 'Factura generada correctamente.']);
    }

    // ── Detalle ───────────────────────────────────────────────────────────────

    public function show($id)
    {
        $factura = Factura::with(['cliente.estrato', 'periodoLectura', 'tarifaPeriodo', 'pagos'])->findOrFail($id);
        return view('facturacion.facturas.show', compact('factura'));
    }

    // ── Pago ──────────────────────────────────────────────────────────────────

    public function registrarPago(Request $request, $id)
    {
        $factura = Factura::findOrFail($id);

        $request->validate([
            'fecha_pago'                       => 'required|date',
            'medio_pago'                       => 'required|in:EFECTIVO,TRANSFERENCIA,CONSIGNACION,DATAFONO,OTRO',
            'numero_recibo'                    => 'nullable|string|max:60',
            'pagos_acueducto'                  => 'nullable|numeric|min:0',
            'pagos_alcantarillado'             => 'nullable|numeric|min:0',
            'pago_otros_cobros_acueducto'      => 'nullable|numeric|min:0',
            'pago_otros_cobros_alcantarillado' => 'nullable|numeric|min:0',
            'observaciones'                    => 'nullable|string',
        ]);

        $pago = new Pago($request->only([
            'fecha_pago','medio_pago','numero_recibo',
            'pagos_acueducto','pagos_alcantarillado',
            'pago_otros_cobros_acueducto','pago_otros_cobros_alcantarillado',
            'pago_conexion_acueducto','pago_conexion_alcantarillado','observaciones',
        ]));

        $pago->factura_id = $factura->id;
        $pago->usuario_id = auth()->id();
        $pago->total_pago_realizado = $pago->calcularTotal();
        $pago->save();

        return response()->json([
            'ok'      => true,
            'saldo'   => $factura->fresh()->saldoPendiente(),
            'estado'  => $factura->fresh()->estado,
            'mensaje' => 'Pago registrado correctamente.',
        ]);
    }

    public function anular(Request $request, $id)
    {
        $factura = Factura::findOrFail($id);

        if ($factura->pagos()->exists()) {
            return response()->json(['ok' => false, 'mensaje' => 'No se puede anular una factura con pagos registrados.'], 422);
        }

        $factura->update(['estado' => 'ANULADA', 'observaciones' => ($factura->observaciones ?? '') . ' | ANULADA: ' . $request->motivo]);

        return response()->json(['ok' => true, 'mensaje' => 'Factura anulada.']);
    }

    // ── Facturación en lote ───────────────────────────────────────────────────

    /** Vista de facturación masiva/manual por lote */
    public function lote()
    {
        $periodos = PeriodoLectura::whereIn('estado', ['ACTIVO','LECTURA_CERRADA','FACTURADO'])
            ->orderBy('codigo', 'desc')->get();

        return view('facturacion.facturas.lote', compact('periodos'));
    }

    /**
     * Ajax: devuelve clientes ACTIVOS sin factura en el período seleccionado.
     * Clasifica: sin_medidor, altos, bajos, causados, normales.
     * "Altos/bajos/causados" vienen del campo Critica de ordenescu para ese período.
     */
    public function clientesSinFactura(Request $request)
    {
        $request->validate(['periodo_lectura_id' => 'required|exists:periodos_lectura,id']);

        $periodo = PeriodoLectura::findOrFail($request->periodo_lectura_id);

        // IDs de clientes que ya tienen factura en este período
        $yaFacturados = Factura::where('periodo_lectura_id', $periodo->id)
            ->pluck('cliente_id')->toArray();

        // Clientes activos sin factura aún
        $clientes = Cliente::with('estrato')
            ->where('estado', 'ACTIVO')
            ->whereNotIn('id', $yaFacturados)
            ->orderBy('sector')
            ->orderBy('suscriptor')
            ->get();

        // Órdenes de lectura para este período (si existen)
        $ordenes = \App\Models\Admin\Ordenesmtl::where('periodo_lectura_id', $periodo->id)
            ->get(['Suscriptor', 'Critica', 'Lect_Actual', 'LA', 'Cons_Act', 'Estado'])
            ->keyBy('Suscriptor');

        $resultado = $clientes->map(function ($c) use ($ordenes) {
            $orden  = $ordenes->get($c->suscriptor);
            $critica = $orden ? ($orden->Critica ?? '') : '';
            $criticaUpper = strtoupper($critica);

            // Clasificación
            if (!$c->tiene_medidor) {
                $tipo = 'sin_medidor';
            } elseif (str_contains($criticaUpper, 'ALTO') || str_contains($criticaUpper, 'ELEVADO')) {
                $tipo = 'alto';
            } elseif (str_contains($criticaUpper, 'BAJO') || str_contains($criticaUpper, 'CERO')) {
                $tipo = 'bajo';
            } elseif ($critica !== '' && !str_contains($criticaUpper, 'NORMAL')) {
                $tipo = 'causado';
            } else {
                $tipo = 'normal';
            }

            return [
                'id'               => $c->id,
                'suscriptor'       => $c->suscriptor,
                'nombre'           => trim($c->nombre . ' ' . $c->apellido),
                'direccion'        => $c->direccion,
                'sector'           => $c->sector,
                'estrato'          => optional($c->estrato)->nombre ?? '—',
                'servicios'        => $c->servicios,
                'tiene_medidor'    => $c->tiene_medidor,
                'serie_medidor'    => $c->serie_medidor,
                'promedio_consumo' => (float) $c->promedio_consumo,
                'tipo'             => $tipo,
                'critica'          => $critica,
                // Pre-llenar con lectura de la orden si existe
                'lect_anterior'    => $orden ? $orden->LA        : null,
                'lect_actual'      => $orden ? $orden->Lect_Actual : null,
                'consumo_sugerido' => $orden ? $orden->Cons_Act  : (int) round($c->promedio_consumo ?: 1),
            ];
        });

        return response()->json([
            'ok'       => true,
            'periodo'  => $periodo->nombre,
            'clientes' => $resultado->values(),
            'total'    => $resultado->count(),
        ]);
    }

    /**
     * Genera facturas en lote para los clientes enviados.
     * Recibe array rows: [{cliente_id, consumo_m3, lectura_anterior, lectura_actual}]
     */
    public function storeLote(Request $request)
    {
        $request->validate([
            'periodo_lectura_id'    => 'required|exists:periodos_lectura,id',
            'rows'                  => 'required|array|min:1|max:500',
            'rows.*.cliente_id'     => 'required|exists:clientes,id',
            'rows.*.consumo_m3'     => 'required|integer|min:0',
            'rows.*.lectura_anterior' => 'nullable|integer|min:0',
            'rows.*.lectura_actual'   => 'nullable|integer|min:0',
        ]);

        $periodo  = PeriodoLectura::with('tarifa')->findOrFail($request->periodo_lectura_id);
        $generadas = 0;
        $errores   = [];

        \DB::beginTransaction();
        try {
            foreach ($request->rows as $row) {
                // Saltar si ya existe factura
                $existe = Factura::where('cliente_id', $row['cliente_id'])
                    ->where('periodo_lectura_id', $periodo->id)
                    ->exists();
                if ($existe) { continue; }

                $cliente = Cliente::with('estrato')->findOrFail($row['cliente_id']);
                $calculo = $this->svc->calcular(
                    $cliente,
                    (int) $row['consumo_m3'],
                    $periodo,
                    isset($row['lectura_anterior']) ? (int) $row['lectura_anterior'] : null,
                    isset($row['lectura_actual'])   ? (int) $row['lectura_actual']   : null
                );
                $calculo['usuario_id']    = auth()->id();
                $calculo['es_automatica'] = false;

                $factura = Factura::create($calculo);

                ClienteHistoricoConsumo::registrarYActualizarPromedio(
                    $cliente->id, $cliente->suscriptor, $periodo->codigo,
                    $calculo['consumo_m3'], $calculo['lectura_anterior'], $calculo['lectura_actual']
                );

                ClienteOtrosCobro::where('cliente_id', $cliente->id)->activo()->each->pagarCuota();

                $generadas++;
            }

            \DB::commit();
        } catch (\Throwable $e) {
            \DB::rollBack();
            return response()->json(['ok' => false, 'mensaje' => 'Error al generar facturas: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'ok'       => true,
            'generadas' => $generadas,
            'mensaje'  => "{$generadas} factura(s) generada(s) correctamente.",
        ]);
    }

    // ── PDF individual ────────────────────────────────────────────────────────

    public function pdf($id)
    {
        $factura = Factura::with(['cliente', 'pagos'])->findOrFail($id);
        $facturas = collect([$factura]);

        $pdf = PDF::loadView('facturacion.facturas.pdf', compact('facturas'))
            ->setPaper('letter', 'portrait');

        return $pdf->download('factura-' . $factura->numero_factura . '.pdf');
    }

    // ── PDF masivo (seleccionados) ─────────────────────────────────────────────

    public function pdfMasivo(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1|max:100',
            'ids.*' => 'integer|exists:facturas,id',
        ]);

        $facturas = Factura::with(['cliente', 'pagos'])
            ->whereIn('id', $request->ids)
            ->orderBy('periodo', 'desc')
            ->orderBy('numero_factura')
            ->get();

        if ($facturas->isEmpty()) {
            abort(404, 'No se encontraron facturas con los IDs indicados.');
        }

        $periodo = $facturas->first()->periodo;
        $nombre  = count($request->ids) === 1
            ? 'factura-' . $facturas->first()->numero_factura . '.pdf'
            : 'facturas-' . $periodo . '-' . count($request->ids) . '.pdf';

        $pdf = PDF::loadView('facturacion.facturas.pdf', compact('facturas'))
            ->setPaper('letter', 'portrait');

        return $pdf->download($nombre);
    }
}
