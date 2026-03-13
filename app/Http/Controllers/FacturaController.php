<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\Pago;
use App\Models\PeriodoLectura;
use App\Models\ClienteHistoricoConsumo;
use App\Models\ClienteOtrosCobro;
use App\Services\FacturacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ZipArchive;
use Barryvdh\DomPDF\Facade as PDF; // Ajusta según tu librería PDF

class FacturaController extends Controller
{
    protected FacturacionService $svc;

    public function __construct(FacturacionService $svc)
    {
        $this->svc = $svc;
    }

    // ── Listado ───────────────────────────────────────────────────────────────

    public function index()
    {
        $periodos = PeriodoLectura::orderBy('codigo', 'desc')->get(['codigo', 'nombre']);
        $rutas    = Cliente::whereNotNull('id_ruta')->distinct()->orderBy('id_ruta')->pluck('id_ruta');
        return view('facturacion.facturas.index', compact('periodos', 'rutas'));
    }

    /** AJAX: DataTables server-side para el listado de facturas */
    public function data(Request $request)
    {
        $query = Factura::leftJoin('clientes', 'clientes.suscriptor', '=', 'facturas.suscriptor')
            ->select('facturas.*', 'clientes.id_ruta as cl_ruta', 'clientes.consecutivo as cl_consecutivo',
                     'clientes.nombre as cl_nombre', 'clientes.apellido as cl_apellido');

        if ($request->filled('periodo'))    $query->where('periodo', $request->periodo);
        if ($request->filled('suscriptor')) $query->where('facturas.suscriptor', 'like', '%'.$request->suscriptor.'%');
        if ($request->filled('estado'))     $query->where('estado', $request->estado);

        if ($request->filled('id_ruta')) {
            $query->where('clientes.id_ruta', $request->id_ruta);
        }

        if ($request->filled('critica')) {
            $subs = \App\Models\Admin\Ordenesmtl::where('Critica', 'like', '%'.$request->critica.'%')->pluck('Suscriptor');
            $query->whereIn('facturas.suscriptor', $subs);
        }

        $total    = Factura::count();
        $filtered = $query->count();

        // Columnas ordenables por índice DataTables
        $colMap = [
            1  => 'facturas.numero_factura',
            2  => 'facturas.suscriptor',
            3  => 'clientes.id_ruta',
            4  => 'clientes.consecutivo',
            5  => 'facturas.periodo',
            6  => 'facturas.fecha_expedicion',
            7  => 'facturas.fecha_vencimiento',
            8  => 'facturas.total_a_pagar',
            10 => 'facturas.estado',
        ];
        $colIdx = (int) $request->input('order.0.column', 6);
        $colDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $colMap[$colIdx] ?? 'facturas.fecha_expedicion';

        $facturas = $query->orderBy($orderCol, $colDir)
            ->skip((int) $request->input('start', 0))
            ->take((int) $request->input('length', 25))
            ->get();

        $data = $facturas->map(function ($f) {
            $nombre = trim(($f->cl_nombre ?? '') . ' ' . ($f->cl_apellido ?? ''));
            if (mb_strlen($nombre) > 22) {
                $nombre = mb_substr($nombre, 0, 22) . '…';
            }

            return [
                'id'           => $f->id,
                'numero'       => $f->numero_factura,
                'suscriptor'   => $f->suscriptor,
                'nombre'       => $nombre,
                'periodo'      => $f->periodo,
                'expedicion'   => $f->fecha_expedicion  ? \Carbon\Carbon::parse($f->fecha_expedicion)->format('d/m/Y')  : '—',
                'vencimiento'  => $f->fecha_vencimiento ? \Carbon\Carbon::parse($f->fecha_vencimiento)->format('d/m/Y') : '—',
                'total'        => number_format($f->total_a_pagar, 0, ',', '.'),
                'estado'       => $f->estado,
                'tipo'         => $f->es_automatica ? 'AUTO' : 'MANUAL',
                'id_ruta'      => $f->cl_ruta,
                'consecutivo'  => $f->cl_consecutivo,
                'url_ver'      => route('facturas.show', $f->id),
                'url_pdf'      => route('facturas.pdf', $f->id),
                'anulada'      => $f->estado === 'ANULADA',
            ];
        });

        return response()->json([
            'draw'            => (int) $request->input('draw', 1),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
    }

    /** AJAX: KPIs (totales por estado) aplicando los mismos filtros */
    public function kpis(Request $request)
    {
        $base = Factura::query();

        if ($request->filled('periodo'))    $base->where('periodo', $request->periodo);
        if ($request->filled('suscriptor')) $base->where('suscriptor', 'like', '%'.$request->suscriptor.'%');
        if ($request->filled('estado'))     $base->where('estado', $request->estado);

        if ($request->filled('id_ruta')) {
            if (\Schema::hasColumn('facturas', 'id_ruta')) {
                $base->where('id_ruta', $request->id_ruta);
            } else {
                $subs = \App\Models\Admin\Ordenesmtl::where('id_Ruta', $request->id_ruta)->pluck('Suscriptor');
                $base->whereIn('suscriptor', $subs);
            }
        }

        if ($request->filled('critica')) {
            $subs = \App\Models\Admin\Ordenesmtl::where('Critica', 'like', '%'.$request->critica.'%')->pluck('Suscriptor');
            $base->whereIn('suscriptor', $subs);
        }

        // KPIs por estado
        $rows = (clone $base)
            ->selectRaw("estado, COUNT(*) as cnt, SUM(total_a_pagar) as total")
            ->groupBy('estado')
            ->get()
            ->keyBy('estado');

        $get = fn($estado) => [
            'cantidad' => (int)   ($rows[$estado]->cnt   ?? 0),
            'total'    => (float) ($rows[$estado]->total ?? 0),
        ];

        // KPIs por tipo de consumo (calculado desde los campos de factura)
        $cats = (clone $base)->selectRaw("
            SUM(CASE WHEN tiene_medidor_snapshot = 0
                THEN 1 ELSE 0 END) as promedio_cnt,
            SUM(CASE WHEN tiene_medidor_snapshot = 0
                THEN COALESCE(total_a_pagar,0) ELSE 0 END) as promedio_total,

            SUM(CASE WHEN tiene_medidor_snapshot = 1 AND consumo_m3 < 0
                THEN 1 ELSE 0 END) as negativa_cnt,
            SUM(CASE WHEN tiene_medidor_snapshot = 1 AND consumo_m3 < 0
                THEN COALESCE(total_a_pagar,0) ELSE 0 END) as negativa_total,

            SUM(CASE WHEN tiene_medidor_snapshot = 1 AND consumo_m3 >= 0
                 AND promedio_consumo_snapshot > 0
                 AND consumo_m3 > promedio_consumo_snapshot * 2
                THEN 1 ELSE 0 END) as alta_cnt,
            SUM(CASE WHEN tiene_medidor_snapshot = 1 AND consumo_m3 >= 0
                 AND promedio_consumo_snapshot > 0
                 AND consumo_m3 > promedio_consumo_snapshot * 2
                THEN COALESCE(total_a_pagar,0) ELSE 0 END) as alta_total,

            SUM(CASE WHEN tiene_medidor_snapshot = 1 AND consumo_m3 >= 0
                 AND promedio_consumo_snapshot > 0
                 AND consumo_m3 < promedio_consumo_snapshot * 0.5
                THEN 1 ELSE 0 END) as baja_cnt,
            SUM(CASE WHEN tiene_medidor_snapshot = 1 AND consumo_m3 >= 0
                 AND promedio_consumo_snapshot > 0
                 AND consumo_m3 < promedio_consumo_snapshot * 0.5
                THEN COALESCE(total_a_pagar,0) ELSE 0 END) as baja_total,

            SUM(CASE WHEN tiene_medidor_snapshot = 1 AND consumo_m3 >= 0
                 AND (promedio_consumo_snapshot = 0
                      OR (consumo_m3 >= promedio_consumo_snapshot * 0.5
                          AND consumo_m3 <= promedio_consumo_snapshot * 2))
                THEN 1 ELSE 0 END) as normal_cnt,
            SUM(CASE WHEN tiene_medidor_snapshot = 1 AND consumo_m3 >= 0
                 AND (promedio_consumo_snapshot = 0
                      OR (consumo_m3 >= promedio_consumo_snapshot * 0.5
                          AND consumo_m3 <= promedio_consumo_snapshot * 2))
                THEN COALESCE(total_a_pagar,0) ELSE 0 END) as normal_total
        ")->first();

        $cat = fn($key) => [
            'cantidad' => (int)   ($cats->{$key.'_cnt'}   ?? 0),
            'total'    => (float) ($cats->{$key.'_total'} ?? 0),
        ];

        return response()->json([
            'pendiente' => $get('PENDIENTE'),
            'pagada'    => $get('PAGADA'),
            'vencida'   => $get('VENCIDA'),
            'anulada'   => $get('ANULADA'),
            'normal'    => $cat('normal'),
            'alta'      => $cat('alta'),
            'baja'      => $cat('baja'),
            'negativa'  => $cat('negativa'),
            'promedio'  => $cat('promedio'),
        ]);
    }

    /** AJAX: Datos para reporte de liquidación (DataTables server-side) */
    public function reporteData(Request $request)
    {
        $query = Factura::with('cliente')->select('facturas.*');

        if ($request->filled('periodo'))    $query->where('periodo', $request->periodo);
        if ($request->filled('suscriptor')) $query->where('suscriptor', 'like', '%'.$request->suscriptor.'%');
        if ($request->filled('estado'))     $query->where('estado', $request->estado);

        if ($request->filled('id_ruta')) {
            if (\Schema::hasColumn('facturas', 'id_ruta')) {
                $query->where('id_ruta', $request->id_ruta);
            } else {
                $subs = \App\Models\Admin\Ordenesmtl::where('id_Ruta', $request->id_ruta)->pluck('Suscriptor');
                $query->whereIn('suscriptor', $subs);
            }
        }

        if ($request->filled('critica')) {
            $subs = \App\Models\Admin\Ordenesmtl::where('Critica', 'like', '%'.$request->critica.'%')->pluck('Suscriptor');
            $query->whereIn('suscriptor', $subs);
        }

        $total    = Factura::count();
        $filtered = $query->count();

        $facturas = $query->orderBy('fecha_expedicion', 'desc')
            ->skip((int) $request->input('start', 0))
            ->take((int) $request->input('length', 50))
            ->get();

        $nf = fn($v) => number_format((float)($v ?? 0), 0, ',', '.');

        $data = $facturas->map(function ($f) use ($nf) {
            $nombre = $f->cliente ? trim($f->cliente->nombre . ' ' . $f->cliente->apellido) : '—';
            return [
                'numero'      => $f->numero_factura,
                'suscriptor'  => $f->suscriptor,
                'nombre'      => $nombre,
                'periodo'     => $f->periodo,
                'estrato'     => $f->estrato_snapshot ?? '—',
                'consumo_m3'  => $f->consumo_m3 ?? 0,
                // Acueducto
                'cf_ac'       => $nf($f->cargo_fijo_acueducto),
                'cb_ac'       => $nf($f->consumo_basico_acueducto_valor),
                'cc_ac'       => $nf($f->consumo_complementario_acueducto_valor),
                'cs_ac'       => $nf($f->consumo_suntuario_acueducto_valor),
                'subsidio_ac' => $nf($f->subsidio_emergencia ?? 0),
                'total_ac'    => $nf($f->total_facturacion_acueducto ?? $f->subtotal_conexion_otros_acueducto),
                // Alcantarillado
                'cf_al'       => $nf($f->cargo_fijo_alcantarillado),
                'cb_al'       => $nf($f->consumo_basico_alcantarillado_valor),
                'cc_al'       => $nf($f->consumo_complementario_alcantarillado_valor),
                'cs_al'       => $nf($f->consumo_suntuario_alcantarillado_valor),
                'subsidio_al' => $nf($f->subsidio_alcantarillado ?? 0),
                'total_al'    => $nf($f->subtotal_alcantarillado ?? $f->subtotal_conexion_otros_alcantarillado),
                // Otros
                'otros_ac'    => $nf($f->cuota_otros_cobros_acueducto ?? 0),
                'otros_al'    => $nf($f->cuota_otros_cobros_alcantarillado ?? 0),
                'saldo_ant'   => $nf($f->saldo_anterior ?? 0),
                'total_pagar' => $nf($f->total_a_pagar),
                'estado'      => $f->estado,
            ];
        });

        return response()->json([
            'draw'            => (int) $request->input('draw', 1),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
    }

    /**
     * Exportar masivamente las facturas del resultado actual en un ZIP
     */
    public function exportarMasivo(Request $request)
    {
        // Repetimos la lógica de filtrado para obtener los IDs exactos
        $query = Factura::with(['cliente']);
        
        if ($request->filled('periodo')) $query->where('periodo', $request->periodo);
        if ($request->filled('suscriptor')) $query->where('suscriptor', 'LIKE', "%{$request->suscriptor}%");
        if ($request->filled('estado')) $query->where('estado', $request->estado);
        if ($request->filled('id_ruta')) {
            $query->whereHas('cliente', fn($q) => $q->where('id_ruta', $request->id_ruta));
        }
        if ($request->filled('critica')) {
            $query->whereHas('cliente', fn($q) => $q->whereHas('ordenes', fn($sq) => $sq->where('Critica', 'LIKE', "%{$request->critica}%")));
        }

        $facturas = $query->get();

        if ($facturas->isEmpty()) {
            return redirect()->back()->with('error', 'No hay facturas para exportar con esos filtros.');
        }

        $zip = new ZipArchive;
        $fileName = "facturas_masivas_" . date('YmdHis') . ".zip";
        $tempPath = storage_path('app/public/' . $fileName);

        if ($zip->open($tempPath, ZipArchive::CREATE) === TRUE) {
            foreach ($facturas as $factura) {
                try {
                    // Generar PDF (la vista espera $facturas como colección)
                    $facturaCol = collect([$factura]);
                    $pdf = PDF::loadView('facturacion.facturas.pdf', ['facturas' => $facturaCol])
                        ->setPaper('letter', 'portrait');

                    // Nombre del archivo dentro del ZIP
                    $nombreArchivo = "Factura_{$factura->numero_factura}_{$factura->suscriptor}.pdf";
                    
                    // Agregar al ZIP
                    $zip->addFromString($nombreArchivo, $pdf->output());
                } catch (\Exception $e) {
                    Log::error("Error generando PDF para factura {$factura->id}: " . $e->getMessage());
                }
            }
            $zip->close();

            // Descargar y eliminar temporal
            return response()->download($tempPath)->deleteFileAfterSend(true);
        }

        return redirect()->back()->with('error', 'Error creando el archivo ZIP.');
    }

    // Nuevo método para exportar seleccionadas
public function exportarSeleccionadas(Request $request)
{
    $request->validate(['ids' => 'required|array']);
    
    $facturas = Factura::with(['cliente', 'periodoLectura'])
        ->whereIn('id', $request->ids)
        ->get();

    if ($facturas->isEmpty()) {
        return back()->with('error', 'No se seleccionaron facturas válidas.');
    }

    // Lógica para generar ZIP con PDFs
    // Usamos la misma lógica del servicio o controlador masivo
    $zip = new \ZipArchive();
    $nombreArchivo = 'facturas_' . date('Y-m-d_H-i-s') . '.zip';
    $rutaTemporal = storage_path('app/public/' . $nombreArchivo);

    if ($zip->open($rutaTemporal, \ZipArchive::CREATE) !== TRUE) {
        return back()->with('error', 'No se pudo crear el archivo ZIP.');
    }

    foreach ($facturas as $f) {
        $facturaCol = collect([$f]);
        $pdf = PDF::loadView('facturacion.facturas.pdf', ['facturas' => $facturaCol])
            ->setPaper('letter', 'portrait');
        $nombrePdf = "Factura_{$f->numero_factura}_{$f->suscriptor}.pdf";
        $zip->addFromString($nombrePdf, $pdf->output());
    }

    $zip->close();

    return response()->download($rutaTemporal)->deleteFileAfterSend(true);
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

        // Verificar que no existe factura vigente (ANULADA permite re-facturar)
        $existe = Factura::where('cliente_id', $request->cliente_id)
            ->where('periodo_lectura_id', $request->periodo_lectura_id)
            ->where('estado', '!=', 'ANULADA')
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
        ClienteOtrosCobro::where('cliente_id', $cliente->id)->activo()->get()->each->pagarCuota();

        return response()->json(['ok' => true, 'factura_id' => $factura->id, 'mensaje' => 'Factura generada correctamente.']);
    }

    // ── Detalle ───────────────────────────────────────────────────────────────

    public function show($id)
    {
        $factura = Factura::with(['cliente.estrato', 'periodoLectura', 'tarifaPeriodo', 'pagos'])->findOrFail($id);

        // Historial de consumo: últimas 6 facturas del mismo cliente (incluyendo la actual)
        $histConsumos = [];
        if ($factura->cliente_id && $factura->periodo) {
            $ultimas = Factura::where('cliente_id', $factura->cliente_id)
                ->where('periodo', '<=', $factura->periodo)
                ->whereNotIn('estado', ['ANULADA'])
                ->orderBy('periodo', 'desc')
                ->limit(6)
                ->get(['periodo', 'consumo_m3']);

            foreach ($ultimas as $f) {
                try {
                    $label = \Carbon\Carbon::createFromFormat('Ym', $f->periodo)->format('M y');
                } catch (\Exception $e) {
                    $label = $f->periodo;
                }
                $histConsumos[] = [
                    'label'      => $label,
                    'consumo_m3' => (float) $f->consumo_m3,
                    'isCurrent'  => $f->periodo === $factura->periodo,
                ];
            }
            $histConsumos = array_reverse($histConsumos); // de más antiguo a más reciente
        }

        return view('facturacion.facturas.show', compact('factura', 'histConsumos'));
    }

    // ── PDF ────────────────────────────────────────────────────────────────────

    public function descargarPdf($id)
    {
        $factura  = Factura::with(['cliente', 'pagos'])->findOrFail($id);
        $facturas = collect([$factura]);

        $pdf = PDF::loadView('facturacion.facturas.pdf', compact('facturas'))
            ->setPaper('letter', 'portrait');

        return $pdf->download('Factura_' . $factura->numero_factura . '_' . $factura->suscriptor . '.pdf');
    }

    // ── Pago ──────────────────────────────────────────────────────────────────

    public function registrarPago(Request $request, $id)
    {
        $factura = Factura::with('pagos')->findOrFail($id);

        // Bloquear si la factura ya está totalmente pagada o anulada
        if (in_array($factura->estado, ['PAGADA', 'ANULADA'])) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Esta factura ya se encuentra ' . $factura->estado . ' y no admite más pagos.',
            ], 422);
        }

        $saldoActual = $factura->saldoPendiente();
        if ($saldoActual <= 0) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'El saldo de esta factura ya está en $0. No se requiere ningún pago adicional.',
            ], 422);
        }

        $request->validate([
            'fecha_pago'                       => 'required|date',
            'medio_pago'                       => 'required|in:EFECTIVO,TRANSFERENCIA,CONSIGNACION,DATAFONO,OTRO',
            'banco'                            => 'nullable|string|max:100',
            'numero_recibo'                    => 'nullable|string|max:60',
            'pagos_acueducto'                  => 'nullable|numeric|min:0',
            'pagos_alcantarillado'             => 'nullable|numeric|min:0',
            'pago_otros_cobros_acueducto'      => 'nullable|numeric|min:0',
            'pago_otros_cobros_alcantarillado' => 'nullable|numeric|min:0',
            'observaciones'                    => 'nullable|string',
        ]);

        $pago = new Pago($request->only([
            'fecha_pago','medio_pago','banco','numero_recibo',
            'pagos_acueducto','pagos_alcantarillado',
            'pago_otros_cobros_acueducto','pago_otros_cobros_alcantarillado',
            'pago_conexion_acueducto','pago_conexion_alcantarillado','observaciones',
        ]));

        // Banco solo aplica a TRANSFERENCIA / CONSIGNACION
        if (!in_array($request->medio_pago, ['TRANSFERENCIA', 'CONSIGNACION'])) {
            $pago->banco = null;
        }

        $pago->factura_id = $factura->id;
        $pago->usuario_id = auth()->id();
        $pago->total_pago_realizado = $pago->calcularTotal();
        $pago->save();

        $facturaFresh = $factura->fresh();
        return response()->json([
            'ok'      => true,
            'saldo'   => $facturaFresh->saldoPendiente(),
            'estado'  => $facturaFresh->estado,
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

        // Revertir cuotas de otros cobros que se descontaron al crear esta factura
        if ($factura->cliente_id) {
            ClienteOtrosCobro::where('cliente_id', $factura->cliente_id)
                ->whereIn('estado', ['ACTIVO', 'PAGADO'])
                ->where('cuotas_pagadas', '>', 0)
                ->get()
                ->each->revertirCuota();
        }

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

        // IDs de clientes que ya tienen factura vigente en este período
        // (las ANULADAS no bloquean, se puede volver a facturar)
        $yaFacturados = Factura::where('periodo_lectura_id', $periodo->id)
            ->where('estado', '!=', 'ANULADA')
            ->pluck('cliente_id')->toArray();

        // Clientes activos sin factura aún
        $clientes = Cliente::with('estrato')
            ->where('estado', 'ACTIVO')
            ->whereNotIn('id', $yaFacturados)
            ->orderBy('sector')
            ->orderBy('suscriptor')
            ->get();

        // Solo órdenes EJECUTADAS en campo (Estado = 4) para este período
        $ordenes = \App\Models\Admin\Ordenesmtl::where('periodo_lectura_id', $periodo->id)
            ->where('Estado', 4)
            ->get([
                'Suscriptor', 'Critica', 'Lect_Actual', 'LA', 'Cons_Act', 'Estado',
                'Observacion_id', 'Observacion_des', 'foto1', 'foto2', 'Promedio',
                'idDivision', 'Ciclo', 'consecutivoRuta', 'Causa_id', 'Causa_des',
            ])
            ->keyBy('Suscriptor');

        // Observacion_id 30,31,32,15,16 → predio desocupado → facturar consumo 0
        // Observacion_id 33             → medidor parado    → facturar con promedio
        $codigosConsumoCero     = [15, 16, 30, 31, 32];
        $codigosPromedioMedidor = [33];

        $resultado = $clientes->map(function ($c) use ($ordenes, $codigosConsumoCero, $codigosPromedioMedidor) {
            $orden = $ordenes->get($c->suscriptor);

            // Con medidor: solo aparece si la lectura fue ejecutada en campo (Estado=4).
            // Sin medidor: siempre aparece; se factura por promedio editable.
            if ($c->tiene_medidor && !$orden) {
                return null;
            }

            $critica      = $orden ? ($orden->Critica ?? '') : '';
            $criticaUpper = strtoupper($critica);

            $observacionId  = $orden ? (int) ($orden->Observacion_id ?? 0) : 0;
            $observacionDes = $orden ? ($orden->Observacion_des ?? '') : '';

            // Promedio real: preferir el registrado en la orden de lectura (más actualizado)
            $promedioOrden   = $orden ? (int) ($orden->Promedio ?? 0) : 0;
            $promedioCliente = (int) round((float) ($c->promedio_consumo ?? 0));
            $promedioReal    = $promedioOrden > 0 ? $promedioOrden : $promedioCliente;

            // Clasificación de tipo
            if (!$c->tiene_medidor) {
                $tipo = 'sin_medidor';
            } elseif (str_contains($criticaUpper, 'IGUAL') && in_array($observacionId, $codigosConsumoCero)) {
                $tipo = 'consumo_cero';      // desocupado → solo básico, consumo 0
            } elseif (str_contains($criticaUpper, 'IGUAL') && in_array($observacionId, $codigosPromedioMedidor)) {
                $tipo = 'promedio_medidor';  // medidor parado → cobrar promedio
            } elseif (str_contains($criticaUpper, 'IGUAL')) {
                $tipo = 'causado';           // iguales sin obs. específica → analista decide
            } elseif (str_contains($criticaUpper, 'ALTO') || str_contains($criticaUpper, 'ELEVADO')) {
                $tipo = 'alto';
            } elseif (str_contains($criticaUpper, 'BAJO')) {
                $tipo = 'bajo';
            } elseif ($critica !== '' && !str_contains($criticaUpper, 'NORMAL')) {
                $tipo = 'causado';
            } else {
                $tipo = 'normal';
            }

            // Consumo sugerido según tipo
            if ($tipo === 'consumo_cero') {
                $consumoSugerido = 0;
            } elseif ($tipo === 'promedio_medidor' || !$orden) {
                $consumoSugerido = $promedioReal;
            } else {
                $consumoSugerido = (int) $orden->Cons_Act;
            }

            // Consumo negativo registrado en campo → separar para revisión del analista
            if ($orden && (int) $orden->Cons_Act < 0) {
                $tipo            = 'negativo';
                $consumoSugerido = (int) $orden->Cons_Act; // mostrar el valor real (negativo) — analista decide el reemplazo
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
                'promedio_consumo' => $promedioReal,
                'tipo'             => $tipo,
                'critica'          => $critica,
                'observacion_id'   => $observacionId,
                'observacion_des'  => $observacionDes,
                'foto1'            => $orden ? ($orden->foto1 ?? null) : null,
                'foto2'            => $orden ? ($orden->foto2 ?? null) : null,
                'lect_anterior'    => $orden ? $orden->LA          : null,
                'lect_actual'      => $orden ? $orden->Lect_Actual : null,
                'consumo_sugerido' => $consumoSugerido,
                'id_ruta'          => $orden ? ($orden->idDivision     ?? null) : null,
                'ciclo'            => $orden ? ($orden->Ciclo           ?? null) : null,
                'consecutivo'      => $orden ? ($orden->consecutivoRuta ?? null) : null,
                'causa_id'         => $orden ? ($orden->Causa_id        ?? null) : null,
                'causa_des'        => $orden ? ($orden->Causa_des       ?? null) : null,
            ];
        })->filter()->values();

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
            'periodo_lectura_id'      => 'required|exists:periodos_lectura,id',
            'observaciones'           => 'nullable|string|max:500',
            'rows'                    => 'required|array|min:1|max:500',
            'rows.*.cliente_id'       => 'required|exists:clientes,id',
            'rows.*.consumo_m3'       => 'required|integer|min:0',
            'rows.*.lectura_anterior' => 'nullable|integer|min:0',
            'rows.*.lectura_actual'   => 'nullable|integer|min:0',
            'rows.*.observacion'      => 'nullable|string|max:500',
        ]);

        $periodo  = PeriodoLectura::with('tarifa')->findOrFail($request->periodo_lectura_id);
        $generadas = 0;
        $errores   = [];

        \DB::beginTransaction();
        try {
            foreach ($request->rows as $row) {
                // Saltar si ya existe factura vigente (ANULADA permite re-facturar)
                $existe = Factura::where('cliente_id', $row['cliente_id'])
                    ->where('periodo_lectura_id', $periodo->id)
                    ->where('estado', '!=', 'ANULADA')
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
                // Observación por fila tiene prioridad; si no, se usa la global del lote
                $obsRow    = trim($row['observacion'] ?? '');
                $obsGlobal = trim($request->observaciones ?? '');
                if ($obsRow) {
                    $calculo['observaciones'] = $obsRow;
                } elseif ($obsGlobal) {
                    $calculo['observaciones'] = $obsGlobal;
                }

                $factura = Factura::create($calculo);

                ClienteHistoricoConsumo::registrarYActualizarPromedio(
                    $cliente->id, $cliente->suscriptor, $periodo->codigo,
                    $calculo['consumo_m3'], $calculo['lectura_anterior'], $calculo['lectura_actual']
                );

                ClienteOtrosCobro::where('cliente_id', $cliente->id)->activo()->get()->each->pagarCuota();

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
        $factura  = Factura::with(['cliente', 'pagos', 'tarifaPeriodo'])->findOrFail($id);
        $facturas = collect([$factura]);
        $empresa  = Empresa::instancia();

        $pdf = PDF::loadView('facturacion.facturas.pdf', compact('facturas', 'empresa'))
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

        $facturas = Factura::with(['cliente', 'pagos', 'tarifaPeriodo'])
            ->whereIn('id', $request->ids)
            ->orderBy('periodo', 'desc')
            ->orderBy('numero_factura')
            ->get();

        if ($facturas->isEmpty()) {
            abort(404, 'No se encontraron facturas con los IDs indicados.');
        }

        $empresa = Empresa::instancia();
        $periodo = $facturas->first()->periodo;
        $nombre  = count($request->ids) === 1
            ? 'factura-' . $facturas->first()->numero_factura . '.pdf'
            : 'facturas-' . $periodo . '-' . count($request->ids) . '.pdf';

        $pdf = PDF::loadView('facturacion.facturas.pdf', compact('facturas', 'empresa'))
            ->setPaper('letter', 'portrait');

        return $pdf->download($nombre);
    }
}
