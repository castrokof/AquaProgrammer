<?php

namespace App\Http\Controllers;

use App\Models\Admin\Ordenesmtl;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\PeriodoLectura;
use App\Models\ClienteHistoricoConsumo;
use App\Models\ClienteOtrosCobro;
use App\Models\OrdenRevision;
use App\Services\FacturacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FacturacionMasivaController extends Controller
{
    protected FacturacionService $svc;

    public function __construct(FacturacionService $svc)
    {
        $this->svc = $svc;
    }

    /**
     * Mostrar vista de facturación masiva
     */
    public function index(Request $request)
    {
        $periodos = PeriodoLectura::whereIn('estado', ['LECTURA_CERRADA', 'FACTURADO'])
            ->orderBy('codigo', 'desc')
            ->get(['id', 'codigo', 'nombre', 'estado']);

        return view('facturacion.facturas.masiva', compact('periodos'));
    }

    /**
     * Ejecutar facturación masiva para un período
     * Solo factura automáticamente las lecturas con crítica NORMAL-54
     * Las demás críticas se marcan para revisión
     */
    public function procesar(Request $request)
    {
        $request->validate([
            'periodo_lectura_id' => 'required|exists:periodos_lectura,id',
        ]);

        $periodo = PeriodoLectura::findOrFail($request->periodo_lectura_id);
        
        // Verificar que el período esté en estado adecuado
        if (!in_array($periodo->estado, ['LECTURA_CERRADA', 'FACTURADO'])) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'El período debe estar en estado LECTURA_CERRADA o FACTURADO para proceder con la facturación.'
            ], 422);
        }

        $resultado = [
            'procesadas' => 0,
            'facturadas_automaticas' => 0,
            'pendientes_revision' => 0,
            'errores' => 0,
            'detalles' => [],
        ];

        DB::beginTransaction();
        try {
            // Obtener todas las lecturas del período que aún no tienen factura
            $lecturas = Ordenesmtl::where('periodo_lectura_id', $request->periodo_lectura_id)
                ->whereNotNull('Lect_Actual')
                ->whereNotNull('LA')
                ->get();

            foreach ($lecturas as $lectura) {
                $resultado['procesadas']++;

                try {
                    // Verificar si ya existe factura para este cliente en este período
                    $existeFactura = Factura::where('suscriptor', $lectura->Suscriptor)
                        ->where('periodo_lectura_id', $request->periodo_lectura_id)
                        ->exists();

                    if ($existeFactura) {
                        $resultado['detalles'][] = [
                            'suscriptor' => $lectura->Suscriptor,
                            'estado' => 'SALTEADO',
                            'mensaje' => 'Ya tiene factura',
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
                            'mensaje' => 'Cliente no encontrado',
                        ];
                        continue;
                    }

                    // Calcular consumo
                    $lecturaAnterior = intval($lectura->LA ?? 0);
                    $lecturaActual = intval($lectura->Lect_Actual ?? 0);
                    $consumo = $lecturaActual - $lecturaAnterior;

                    // Si el consumo es negativo, saltar
                    if ($consumo < 0) {
                        $resultado['errores']++;
                        $resultado['detalles'][] = [
                            'suscriptor' => $lectura->Suscriptor,
                            'estado' => 'ERROR',
                            'mensaje' => 'Consumo negativo (' . $consumo . ')',
                        ];
                        continue;
                    }

                    // Determinar tipo de procesamiento según la crítica
                    $critica = strtoupper(trim($lectura->Critica ?? ''));
                    
                    // Solo NORMAL-54 o 54-NORMAL se facturan automáticamente
                    $esNormal = in_array($critica, ['NORMAL-54', '54-NORMAL']);

                    if ($esNormal) {
                        // Facturación automática para NORMAL-54
                        $calculo = $this->svc->calcular(
                            $cliente,
                            $consumo,
                            $periodo,
                            $lecturaAnterior,
                            $lecturaActual
                        );

                        $calculo['observaciones'] = 'Facturación automática - Lectura NORMAL-54';
                        $calculo['usuario_id'] = auth()->id();
                        $calculo['es_automatica'] = true;
                        $calculo['suscriptor'] = $lectura->Suscriptor;
                        $calculo['periodo_lectura_id'] = $periodo->id;

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

                        // Descontar cuotas de otros cobros - CORRECCIÓN: usar bucle foreach en lugar de each
                        $otrosCobros = ClienteOtrosCobro::where('cliente_id', $cliente->id)->activo()->get();
                        foreach ($otrosCobros as $cobro) {
                            $cobro->pagarCuota();
                        }

                        $resultado['facturadas_automaticas']++;
                        $resultado['detalles'][] = [
                            'suscriptor' => $lectura->Suscriptor,
                            'estado' => 'FACTURADO_AUTOMATICO',
                            'mensaje' => 'Factura #' . $factura->numero_factura,
                            'consumo' => $consumo,
                            'critica' => $critica,
                        ];

                    } else {
                        // Otras críticas van a revisión manual
                        // Crear orden de revisión si no existe
                        $revisionExistente = OrdenRevision::where('lectura_id', $lectura->id)->exists();
                        
                        if (!$revisionExistente) {
                            OrdenRevision::crearDesdeLectura($lectura, auth()->id(), 'REVISION_FACTURACION');
                        }

                        $resultado['pendientes_revision']++;
                        $resultado['detalles'][] = [
                            'suscriptor' => $lectura->Suscriptor,
                            'estado' => 'PENDIENTE_REVISION',
                            'mensaje' => 'Crítica: ' . $critica,
                            'consumo' => $consumo,
                            'critica' => $critica,
                        ];
                    }

                } catch (\Exception $e) {
                    $resultado['errores']++;
                    $resultado['detalles'][] = [
                        'suscriptor' => $lectura->Suscriptor,
                        'estado' => 'ERROR',
                        'mensaje' => $e->getMessage(),
                    ];
                    Log::error('Error en facturación masiva: ' . $e->getMessage(), [
                        'suscriptor' => $lectura->Suscriptor,
                        'lectura_id' => $lectura->id,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'resultado' => $resultado,
                'mensaje' => 'Proceso completado. ' . 
                    $resultado['facturadas_automaticas'] . ' facturadas automáticamente, ' .
                    $resultado['pendientes_revision'] . ' pendientes de revisión.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error grave en facturación masiva: ' . $e->getMessage());
            
            return response()->json([
                'ok' => false,
                'mensaje' => 'Error durante el proceso: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener resumen de lecturas por período
     */
    public function resumen(Request $request)
    {
        $request->validate([
            'periodo_lectura_id' => 'required|exists:periodos_lectura,id',
        ]);

        $periodo = PeriodoLectura::findOrFail($request->periodo_lectura_id);

        $total = Ordenesmtl::where('periodo_lectura_id', $request->periodo_lectura_id)
            ->whereNotNull('Lect_Actual')
            ->count();

        $normales = Ordenesmtl::where('periodo_lectura_id', $request->periodo_lectura_id)
            ->whereIn('Critica', ['NORMAL-54', '54-NORMAL'])
            ->count();

        $conFactura = Factura::where('periodo_lectura_id', $request->periodo_lectura_id)->count();

        return response()->json([
            'ok' => true,
            'resumen' => [
                'total_lecturas' => $total,
                'normales_54' => $normales,
                'otras_criticas' => $total - $normales,
                'ya_facturadas' => $conFactura,
                'pendientes_facturar' => $total - $conFactura,
            ],
        ]);
    }
}
