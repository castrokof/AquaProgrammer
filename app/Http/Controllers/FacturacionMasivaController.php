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
     * Obtener lecturas de un período para selección manual
     */
    public function obtenerLecturas(Request $request)
    {
        $request->validate([
            'periodo_lectura_id' => 'required|exists:periodos_lectura,id',
        ]);

        $periodo = PeriodoLectura::findOrFail($request->periodo_lectura_id);

        // Obtener todas las lecturas del período
        $lecturas = Ordenesmtl::where('periodo_lectura_id', $request->periodo_lectura_id)
            ->whereNotNull('Lect_Actual')
            ->whereNotNull('LA')
            ->with(['cliente' => function($q) {
                $q->select('id', 'suscriptor', 'nombre', 'apellido');
            }])
            ->get()
            ->map(function($lectura) {
                $consumo = intval($lectura->Lect_Actual ?? 0) - intval($lectura->LA ?? 0);
                $tieneFactura = Factura::where('suscriptor', $lectura->Suscriptor)
                    ->where('periodo_lectura_id', $lectura->periodo_lectura_id)
                    ->exists();
                
                $tieneRevision = OrdenRevision::where('lectura_id', $lectura->id)
                    ->whereIn('estado_orden', ['PENDIENTE', 'EN_PROGRESO'])
                    ->exists();

                return [
                    'id' => $lectura->id,
                    'suscriptor' => $lectura->Suscriptor,
                    'cliente' => $lectura->cliente ? trim($lectura->cliente->nombre . ' ' . $lectura->cliente->apellido) : 'N/A',
                    'lectura_anterior' => intval($lectura->LA ?? 0),
                    'lectura_actual' => intval($lectura->Lect_Actual ?? 0),
                    'consumo' => $consumo,
                    'critica' => trim($lectura->Critica ?? ''),
                    'tiene_factura' => $tieneFactura,
                    'tiene_revision' => $tieneRevision,
                    'es_normal' => in_array(strtoupper(trim($lectura->Critica ?? '')), ['NORMAL-54', '54-NORMAL']),
                ];
            });

        return response()->json([
            'ok' => true,
            'lecturas' => $lecturas,
        ]);
    }

    /**
     * Generar facturas para lecturas seleccionadas manualmente
     */
    public function procesarSeleccionadas(Request $request)
    {
        $request->validate([
            'periodo_lectura_id' => 'required|exists:periodos_lectura,id',
            'lecturas_ids' => 'required|array|min:1',
            'lecturas_ids.*' => 'required|exists:ordenesmtl,id',
        ]);

        $periodo = PeriodoLectura::findOrFail($request->periodo_lectura_id);
        
        if (!in_array($periodo->estado, ['LECTURA_CERRADA', 'FACTURADO'])) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'El período debe estar en estado LECTURA_CERRADA o FACTURADO.'
            ], 422);
        }

        $resultado = [
            'procesadas' => 0,
            'facturadas' => 0,
            'errores' => 0,
            'detalles' => [],
        ];

        DB::beginTransaction();
        try {
            foreach ($request->lecturas_ids as $lecturaId) {
                $resultado['procesadas']++;

                try {
                    $lectura = Ordenesmtl::findOrFail($lecturaId);

                    // Verificar si ya existe factura
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

                    if ($consumo < 0) {
                        $resultado['errores']++;
                        $resultado['detalles'][] = [
                            'suscriptor' => $lectura->Suscriptor,
                            'estado' => 'ERROR',
                            'mensaje' => 'Consumo negativo (' . $consumo . ')',
                        ];
                        continue;
                    }

                    // Facturar
                    $calculo = $this->svc->calcular(
                        $cliente,
                        $consumo,
                        $periodo,
                        $lecturaAnterior,
                        $lecturaActual
                    );

                    $calculo['observaciones'] = 'Facturación manual selectiva';
                    $calculo['usuario_id'] = auth()->id();
                    $calculo['es_automatica'] = false;
                    $calculo['suscriptor'] = $lectura->Suscriptor;
                    $calculo['periodo_lectura_id'] = $periodo->id;

                    $factura = Factura::create($calculo);

                    // Registrar en historial
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
                        'estado' => 'FACTURADO',
                        'mensaje' => 'Factura #' . $factura->numero_factura,
                        'consumo' => $consumo,
                    ];

                } catch (\Exception $e) {
                    $resultado['errores']++;
                    $resultado['detalles'][] = [
                        'suscriptor' => $lectura->Suscriptor ?? 'N/A',
                        'estado' => 'ERROR',
                        'mensaje' => $e->getMessage(),
                    ];
                    Log::error('Error en facturación selectiva: ' . $e->getMessage(), [
                        'lectura_id' => $lecturaId,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'resultado' => $resultado,
                'mensaje' => 'Proceso completado. ' . $resultado['facturadas'] . ' facturas generadas.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error grave en facturación selectiva: ' . $e->getMessage());
            
            return response()->json([
                'ok' => false,
                'mensaje' => 'Error durante el proceso: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generar órdenes de revisión para lecturas seleccionadas
     */
    public function generarRevisiones(Request $request)
    {
        $request->validate([
            'periodo_lectura_id' => 'required|exists:periodos_lectura,id',
            'lecturas_ids' => 'required|array|min:1',
            'lecturas_ids.*' => 'required|exists:ordenesmtl,id',
        ]);

        $resultado = [
            'procesadas' => 0,
            'revisiones_creadas' => 0,
            'omitidas' => 0,
            'errores' => 0,
            'detalles' => [],
        ];

        DB::beginTransaction();
        try {
            foreach ($request->lecturas_ids as $lecturaId) {
                $resultado['procesadas']++;

                try {
                    $lectura = Ordenesmtl::findOrFail($lecturaId);

                    // Verificar si ya tiene revisión pendiente
                    $revisionExistente = OrdenRevision::where('lectura_id', $lectura->id)
                        ->whereIn('estado_orden', ['PENDIENTE', 'EN_PROGRESO'])
                        ->exists();

                    if ($revisionExistente) {
                        $resultado['omitidas']++;
                        $resultado['detalles'][] = [
                            'suscriptor' => $lectura->Suscriptor,
                            'estado' => 'OMITIDA',
                            'mensaje' => 'Ya tiene revisión pendiente',
                        ];
                        continue;
                    }

                    // Crear orden de revisión
                    $revision = OrdenRevision::crearDesdeLectura($lectura, auth()->id(), 'REVISION_MANUAL');

                    $resultado['revisiones_creadas']++;
                    $resultado['detalles'][] = [
                        'suscriptor' => $lectura->Suscriptor,
                        'estado' => 'REVISION_CREADA',
                        'mensaje' => 'Revisión #' . $revision->id,
                        'critica' => trim($lectura->Critica ?? ''),
                    ];

                } catch (\Exception $e) {
                    $resultado['errores']++;
                    $resultado['detalles'][] = [
                        'suscriptor' => $lectura->Suscriptor ?? 'N/A',
                        'estado' => 'ERROR',
                        'mensaje' => $e->getMessage(),
                    ];
                    Log::error('Error al crear revisión: ' . $e->getMessage(), [
                        'lectura_id' => $lecturaId,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'resultado' => $resultado,
                'mensaje' => 'Proceso completado. ' . $resultado['revisiones_creadas'] . ' revisiones creadas.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error grave al generar revisiones: ' . $e->getMessage());
            
            return response()->json([
                'ok' => false,
                'mensaje' => 'Error durante el proceso: ' . $e->getMessage(),
            ], 500);
        }
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
     * Generar facturas masivamente para lecturas críticas confirmadas
     * Esta función factura todas las lecturas que han sido revisadas y confirmadas
     */
    public function procesarCriticasConfirmadas(Request $request)
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
            'facturadas' => 0,
            'errores' => 0,
            'detalles' => [],
        ];

        DB::beginTransaction();
        try {
            // Obtener todas las órdenes de revisión EJECUTADAS del período que tengan nueva_lectura
            $ordenesRevision = OrdenRevision::whereHas('lectura', function($q) use ($request) {
                    $q->where('periodo_lectura_id', $request->periodo_lectura_id);
                })
                ->where('estado_orden', 'EJECUTADO')
                ->whereNotNull('nueva_lectura')
                ->get();

            foreach ($ordenesRevision as $orden) {
                $resultado['procesadas']++;

                try {
                    $lectura = $orden->lectura;
                    
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

                    // Usar la nueva lectura confirmada
                    $lecturaAnterior = intval($lectura->LA ?? 0);
                    $lecturaActual = intval($orden->nueva_lectura ?? 0);
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

                    // Facturar con la lectura confirmada
                    $calculo = $this->svc->calcular(
                        $cliente,
                        $consumo,
                        $periodo,
                        $lecturaAnterior,
                        $lecturaActual
                    );

                    $calculo['observaciones'] = 'Facturación por crítica confirmada - Revisión #' . $orden->id;
                    $calculo['usuario_id'] = auth()->id();
                    $calculo['es_automatica'] = false;
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

                    // Descontar cuotas de otros cobros
                    $otrosCobros = ClienteOtrosCobro::where('cliente_id', $cliente->id)->activo()->get();
                    foreach ($otrosCobros as $cobro) {
                        $cobro->pagarCuota();
                    }

                    $resultado['facturadas']++;
                    $resultado['detalles'][] = [
                        'suscriptor' => $lectura->Suscriptor,
                        'estado' => 'FACTURADO',
                        'mensaje' => 'Factura #' . $factura->numero_factura,
                        'consumo' => $consumo,
                        'revision_id' => $orden->id,
                    ];

                } catch (\Exception $e) {
                    $resultado['errores']++;
                    $resultado['detalles'][] = [
                        'suscriptor' => $orden->codigo_predio ?? 'N/A',
                        'estado' => 'ERROR',
                        'mensaje' => $e->getMessage(),
                    ];
                    Log::error('Error en facturación de críticas confirmadas: ' . $e->getMessage(), [
                        'orden_revision_id' => $orden->id,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'resultado' => $resultado,
                'mensaje' => 'Proceso completado. ' . 
                    $resultado['facturadas'] . ' facturas generadas desde críticas confirmadas.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error grave en facturación de críticas confirmadas: ' . $e->getMessage());
            
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

        // Contar revisiones ejecutadas pendientes de facturar
        $revisionesEjecutadas = OrdenRevision::whereHas('lectura', function($q) use ($request) {
                $q->where('periodo_lectura_id', $request->periodo_lectura_id);
            })
            ->where('estado_orden', 'EJECUTADO')
            ->whereNotNull('nueva_lectura')
            ->count();

        return response()->json([
            'ok' => true,
            'resumen' => [
                'total_lecturas' => $total,
                'normales_54' => $normales,
                'otras_criticas' => $total - $normales,
                'ya_facturadas' => $conFactura,
                'pendientes_facturar' => $total - $conFactura,
                'revisiones_ejecutadas' => $revisionesEjecutadas,
            ],
        ]);
    }

    /**
     * Descargar masivamente facturas en PDF (ZIP)
     */
    public function descargarMasivo(Request $request)
    {
        $request->validate([
            'facturas_ids' => 'required|array',
            'facturas_ids.*' => 'required|integer|exists:facturas,id',
        ]);

        try {
            $zip = new \ZipArchive();
            $zipFileName = 'facturas_' . date('Y-m-d_His') . '.zip';
            $zipPath = storage_path('app/temp/' . $zipFileName);

            // Crear directorio temporal si no existe
            if (!file_exists(dirname($zipPath))) {
                mkdir(dirname($zipPath), 0755, true);
            }

            if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
                return response()->json([
                    'ok' => false,
                    'mensaje' => 'Error al crear archivo ZIP'
                ], 500);
            }

            $contador = 0;
            foreach ($request->facturas_ids as $facturaId) {
                $factura = Factura::with(['cliente.estrato', 'periodoLectura', 'tarifaPeriodo'])
                    ->findOrFail($facturaId);

                $pdf = \PDF::loadView('pdf.factura', compact('factura'));
                
                $pdfContent = $pdf->output();
                $filename = sprintf('Factura_%s_%s.pdf', 
                    $factura->numero_factura, 
                    $factura->suscriptor
                );

                $zip->addFromString($filename, $pdfContent);
                $contador++;
            }

            $zip->close();

            return response()->download($zipPath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Error en descarga masiva: ' . $e->getMessage());
            
            return response()->json([
                'ok' => false,
                'mensaje' => 'Error durante la generación del ZIP: ' . $e->getMessage(),
            ], 500);
        }
    }
}
