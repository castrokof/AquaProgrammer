<?php

namespace App\Http\Controllers;

use App\Models\Admin\Ordenesmtl;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\PeriodoLectura;
use App\Models\ClienteHistoricoConsumo;
use App\Models\ClienteOtrosCobro;
use App\Services\FacturacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FacturacionEspecialController extends Controller
{
    protected FacturacionService $svc;

    public function __construct(FacturacionService $svc)
    {
        $this->svc = $svc;
    }

    /**
     * Mostrar vista de facturación especial con DataTable
     * Muestra solo lecturas NO normales (!= 54-NORMAL) y sin factura
     */
    public function index(Request $request)
    {
        $periodos = PeriodoLectura::whereIn('estado', ['LECTURA_CERRADA', 'FACTURADO'])
            ->orderBy('codigo', 'desc')
            ->get(['id', 'codigo', 'nombre', 'estado']);

        $periodoId = $request->input('periodo_id');
        $lecturas = collect();

        if ($periodoId) {
            // Obtener lecturas que NO son normales y aún no tienen factura
            $lecturasQuery = Ordenesmtl::where('periodo_lectura_id', $periodoId)
                ->whereNotNull('Lect_Actual')
                ->whereNotNull('LA')
                ->whereNotIn('Critica', ['NORMAL-54', '54-NORMAL']); // Excluir automáticas

            $lecturas = $lecturasQuery->get()->filter(function($l) use ($periodoId) {
                // Filtrar en memoria las que ya tengan factura para este período
                return !Factura::where('suscriptor', $l->Suscriptor)
                    ->where('periodo_lectura_id', $periodoId)
                    ->exists();
            });
        }

        return view('facturacion.especial.index', compact('periodos', 'lecturas', 'periodoId'));
    }

    /**
     * Obtener resumen de lecturas ESPECIALES por período
     * (Excluye las normales 54)
     */
    public function resumen(Request $request)
    {
        $request->validate([
            'periodo_lectura_id' => 'required|exists:periodos_lectura,id',
        ]);

        $periodo = PeriodoLectura::findOrFail($request->periodo_lectura_id);

        // Total de lecturas con datos válidos
        $total = Ordenesmtl::where('periodo_lectura_id', $request->periodo_lectura_id)
            ->whereNotNull('Lect_Actual')
            ->whereNotNull('LA')
            ->count();

        // Cantidad de lecturas NORMALES (que se facturan automático)
        $normales = Ordenesmtl::where('periodo_lectura_id', $request->periodo_lectura_id)
            ->whereIn('Critica', ['NORMAL-54', '54-NORMAL'])
            ->count();

        // Cantidad de lecturas ESPECIALES (el resto)
        $especiales = $total - $normales;

        // Cuántas de esas especiales YA tienen factura
        // Nota: Esto requiere un poco más de procesamiento o una subconsulta si hay muchas
        // Para simplificar, contamos todas las facturas del período y restamos las automáticas estimadas
        // O mejor, hacemos un conteo directo sobre la tabla facturas cruzando suscriptores de lecturas especiales
        
        // Obtener suscriptores de lecturas especiales
        $suscriptoresEspeciales = Ordenesmtl::where('periodo_lectura_id', $request->periodo_lectura_id)
            ->whereNotIn('Critica', ['NORMAL-54', '54-NORMAL'])
            ->pluck('Suscriptor');

        $conFacturaEspecial = Factura::whereIn('suscriptor', $suscriptoresEspeciales)
            ->where('periodo_lectura_id', $request->periodo_lectura_id)
            ->count();

        return response()->json([
            'ok' => true,
            'resumen' => [
                'total_lecturas' => $total,
                'normales_54' => $normales,
                'especiales_total' => $especiales,
                'especiales_facturadas' => $conFacturaEspecial,
                'especiales_pendientes' => $especiales - $conFacturaEspecial,
            ],
        ]);
    }

    public function getLecturas(Request $request)
{
    $request->validate([
        'periodo_lectura_id' => 'required|exists:periodos_lectura,id',
    ]);

    $periodoId = $request->periodo_lectura_id;

    // Obtener lecturas que NO son normales
    $lecturasQuery = Ordenesmtl::where('periodo_lectura_id', $periodoId)
        ->whereNotNull('Lect_Actual')
        ->whereNotNull('LA')
        ->whereNotIn('Critica', ['NORMAL-54', '54-NORMAL']);

    $lecturasRaw = $lecturasQuery->get();

    // Filtrar en memoria las que ya tienen factura y construir el array de datos
    $lecturasParaJS = [];

    foreach ($lecturasRaw as $l) {
        // Verificar si ya tiene factura
        $tieneFactura = Factura::where('suscriptor', $l->Suscriptor)
            ->where('periodo_lectura_id', $periodoId)
            ->exists();

        if (!$tieneFactura) {
            // Buscar nombre del cliente (opcional, si quieres mostrarlo en la tabla)
            $cliente = Cliente::where('suscriptor', $l->Suscriptor)->first();
            $nombreCliente = $cliente ? $cliente->nombre : 'N/A';

            $la = intval($l->LA ?? 0);
            $lectActual = intval($l->Lect_Actual ?? 0);
            $consumo = $lectActual - $la;

            $lecturasParaJS[] = [
                'lectura_id' => $l->id,
                'suscriptor' => $l->Suscriptor,
                'nombre' => $nombreCliente,
                'la' => $la,
                'lect_actual' => $lectActual,
                'consumo' => $consumo,
                'critica' => $l->Critica ?? 'SIN_CRITICA',
            ];
        }
    }

    return response()->json([
        'ok' => true,
        'lecturas' => $lecturasParaJS,
    ]);
}
    /**
     * Facturar las lecturas especiales seleccionadas manualmente
     */
    public function facturarSeleccionadas(Request $request)
    {
        $request->validate([
            'periodo_lectura_id' => 'required|exists:periodos_lectura,id',
            'lecturas' => 'required|array|min:1',
        ]);

        $periodo = PeriodoLectura::findOrFail($request->periodo_lectura_id);
        
        $resultado = [
            'procesadas' => 0,
            'facturadas' => 0,
            'errores' => 0,
            'detalles' => [],
        ];

        DB::beginTransaction();
        try {
            foreach ($request->lecturas as $lecturaData) {
                $resultado['procesadas']++;

                try {
                    $lecturaId = $lecturaData['lectura_id'] ?? null;
                    if (!$lecturaId) {
                        throw new \Exception('ID de lectura no proporcionado');
                    }

                    $lectura = Ordenesmtl::findOrFail($lecturaId);

                    // Verificar si ya existe factura (doble seguridad)
                    $existeFactura = Factura::where('suscriptor', $lectura->Suscriptor)
                        ->where('periodo_lectura_id', $request->periodo_lectura_id)
                        ->exists();

                    if ($existeFactura) {
                        $resultado['detalles'][] = [
                            'suscriptor' => $lectura->Suscriptor,
                            'estado' => 'SALTEADO',
                            'mensaje' => 'Ya tiene factura generada',
                        ];
                        continue;
                    }

                    // Buscar cliente
                    $cliente = Cliente::where('suscriptor', $lectura->Suscriptor)->first();
                    if (!$cliente) {
                        $resultado['errores']++;
                        $resultado['detalles'][] = [
                            'suscriptor' => $lectura->Suscriptor,
                            'estado' => 'ERROR',
                            'mensaje' => 'Cliente no encontrado en base de datos',
                        ];
                        continue;
                    }

                    // Calcular consumo
                    $lecturaAnterior = intval($lecturaData['la'] ?? $lectura->LA ?? 0);
                    $lecturaActual = intval($lecturaData['lect_actual'] ?? $lectura->Lect_Actual ?? 0);
                    $consumo = $lecturaActual - $lecturaAnterior;

                    if ($consumo < 0) {
                        $resultado['errores']++;
                        $resultado['detalles'][] = [
                            'suscriptor' => $lectura->Suscriptor,
                            'estado' => 'ERROR',
                            'mensaje' => 'Consumo negativo (' . $consumo . ')',
                        ];
                        continue;
                    }

                    // Ejecutar cálculo del servicio
                    $calculo = $this->svc->calcular(
                        $cliente,
                        $consumo,
                        $periodo,
                        $lecturaAnterior,
                        $lecturaActual
                    );

                    // Preparar datos para creación de factura
                    $calculo['observaciones'] = 'Facturación Especial Manual - Crítica: ' . ($lectura->Critica ?? 'SIN_CRITICA');
                    $calculo['usuario_id'] = auth()->id();
                    $calculo['es_automatica'] = false;
                    $calculo['suscriptor'] = $lectura->Suscriptor;
                    $calculo['periodo_lectura_id'] = $periodo->id;

                    // Crear factura
                    $factura = Factura::create($calculo);

                    // Registrar en historial de consumos
                    ClienteHistoricoConsumo::registrarYActualizarPromedio(
                        $cliente->id,
                        $cliente->suscriptor,
                        $periodo->codigo,
                        $calculo['consumo_m3'],
                        $lecturaAnterior,
                        $lecturaActual
                    );

                    // Descontar cuotas de otros cobros
                    $otrosCobros = ClienteOtrosCobro::where('cliente_id', $cliente->id)->activo()->get();
                    foreach ($otrosCobros as $cobro) {
                        $cobro->pagarCuota();
                    }

                    $resultado['facturadas']++;
                    $resultado['detalles'][] = [
                        'suscriptor' => $lectura->Suscriptor,
                        'estado' => 'FACTURADO_ESPECIAL',
                        'mensaje' => 'Factura #' . $factura->numero_factura,
                        'consumo' => $consumo,
                        'critica' => $lectura->Critica ?? '—',
                    ];

                } catch (\Exception $e) {
                    $resultado['errores']++;
                    $resultado['detalles'][] = [
                        'suscriptor' => $lecturaData['suscriptor'] ?? 'DESCONOCIDO',
                        'estado' => 'ERROR',
                        'mensaje' => $e->getMessage(),
                    ];
                    Log::error('Error en facturación especial: ' . $e->getMessage(), [
                        'lectura_data' => $lecturaData,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'resultado' => $resultado,
                'mensaje' => 'Proceso completado. ' . 
                    $resultado['facturadas'] . ' facturas especiales generadas, ' .
                    $resultado['errores'] . ' errores.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error grave en facturación especial: ' . $e->getMessage());
            
            return response()->json([
                'ok' => false,
                'mensaje' => 'Error crítico durante el proceso: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Descargar PDF individual desde esta vista
     */
    public function descargarPdf($id)
    {
        return $this->svc->descargarPdf($id);
    }
}