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
use Illuminate\Support\Facades\Log;
use ZipArchive;
use Barryvdh\DomPDF\Facade\Pdf; // Ajusta según tu librería PDF

class FacturaController extends Controller
{
    protected FacturacionService $svc;

    public function __construct(FacturacionService $svc)
    {
        $this->svc = $svc;
    }

    // ── Listado ───────────────────────────────────────────────────────────────

    // public function index(Request $request)
    // {
    //     $periodos = PeriodoLectura::orderBy('codigo', 'desc')->get(['id','codigo','nombre','estado']);

    //     $query = Factura::with('cliente')
    //         ->orderBy('periodo', 'desc')
    //         ->orderBy('numero_factura', 'desc');

    //     if ($p = $request->periodo) $query->where('periodo', $p);
    //     if ($s = $request->suscriptor) $query->where('suscriptor', 'like', "%{$s}%");
    //     if ($e = $request->estado) $query->where('estado', $e);

    //     $facturas = $query->paginate(25)->appends(request()->query());

    //     return view('facturacion.facturas.index', compact('facturas', 'periodos'));
    // }

    public function index(Request $request)
{
    $periodos = PeriodoLectura::orderBy('codigo', 'desc')->get(['codigo', 'nombre']);
    
    // Iniciamos la consulta
    $query = Factura::with(['cliente', 'periodoLectura'])
        ->select('facturas.*'); // Seleccionar explícitamente columnas de facturas

    // Aplicar Filtros
    if ($request->filled('periodo')) {
        $query->where('periodo', $request->periodo);
    }
    if ($request->filled('suscriptor')) {
        $query->where('suscriptor', 'like', '%' . $request->suscriptor . '%');
    }
    if ($request->filled('estado')) {
        $query->where('estado', $request->estado);
    }
    // NUEVOS FILTROS
    if ($request->filled('id_ruta')) {
        // Unimos con la tabla ordenescu para filtrar por ruta histórica o usamos el snapshot si lo guardaste
        // Opción A: Si guardaste id_ruta en facturas (recomendado verificar si existe en tu BD)
        if (\Schema::hasColumn('facturas', 'id_ruta')) {
            $query->where('id_ruta', $request->id_ruta);
        } else {
            // Opción B: Filtrar por suscriptores que pertenezcan a esa ruta en la última lectura
            $suscriptoresRuta = \App\Models\Admin\Ordenesmtl::where('id_Ruta', $request->id_ruta)
                ->pluck('Suscriptor');
            $query->whereIn('suscriptor', $suscriptoresRuta);
        }
    }
    
    if ($request->filled('critica')) {
        // Similar a ruta, la crítica viene de la orden de lectura
        $suscriptoresCritica = \App\Models\Admin\Ordenesmtl::where('Critica', $request->critica)
            ->pluck('Suscriptor');
        $query->whereIn('suscriptor', $suscriptoresCritica);
    }

    // Ordenamiento por defecto
    $query->orderBy('fecha_expedicion', 'desc');

    // Obtenemos TODOS los resultados para que DataTables funcione bien con filtros y exportación
    // Si son demasiados miles, considera implementar Server-side processing, pero para < 5000 esto es ideal.
    $facturas = $query->get();

    // KPIs Dinámicos basados en los filtros aplicados
    $kpiTotal = $facturas->count();
    $kpiPendiente = $facturas->where('estado', 'PENDIENTE')->sum('total_a_pagar');
    $kpiPagada = $facturas->where('estado', 'PAGADA')->sum('total_a_pagar');
    
    // Agrupación por Crítica (para las tarjetas extra)
    // Nota: Esto requiere unir con ordenes nuevamente para obtener la crítica actual de cada factura
    $facturasConCritica = $facturas->map(function($f) {
        $orden = \App\Models\Admin\Ordenesmtl::where('Suscriptor', $f->suscriptor)
            ->where('periodo_lectura_id', $f->periodo_lectura_id)
            ->first();
        return [
            'factura' => $f,
            'critica' => $orden ? $orden->Critica : 'N/A',
            'id_ruta' => $orden ? $orden->id_Ruta : 'N/A'
        ];
    });

    $agrupadoPorCritica = collect($facturasConCritica)->groupBy('critica')->map(function($items, $key) {
        return [
            'cantidad' => $items->count(),
            'total_valor' => $items->sum(fn($i) => $i['factura']->total_a_pagar)
        ];
    });

    return view('facturacion.facturas.index', compact(
        'facturas', 
        'facturasConCritica', // Pasamos la colección enriquecida para la vista
        'periodos', 
        'kpiTotal', 
        'kpiPendiente', 
        'kpiPagada',
        'agrupadoPorCritica'
    ));
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
                    // Generar PDF
                    $pdf = Pdf::loadView('facturacion.facturas.pdf', compact('factura')); // Ajusta tu vista PDF
                    
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
        // Generar PDF individual (usando tu servicio existente)
        $pdf = \PDF::loadView('facturacion.facturas.pdf', compact('f')); // Ajusta la vista según tu proyecto
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

    // ── PDF ────────────────────────────────────────────────────────────────────

    public function descargarPdf($id)
    {
        $factura = Factura::with(['cliente.estrato', 'periodoLectura', 'tarifaPeriodo', 'pagos'])->findOrFail($id);
        
        $pdf = \PDF::loadView('pdf.factura', compact('factura'));
        
        $filename = sprintf('Factura_%s_%s.pdf', 
            $factura->numero_factura, 
            $factura->suscriptor
        );
        
        return $pdf->download($filename);
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
}
