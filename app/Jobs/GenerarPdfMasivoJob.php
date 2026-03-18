<?php

namespace App\Jobs;

use App\Models\Empresa;
use App\Models\Exportacion;
use App\Models\Factura;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class GenerarPdfMasivoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800; // 30 minutos máximo (aumentado para lotes grandes)
    public $tries   = 1;   // No reintentar si falla

    protected int $exportacionId;

    public function __construct(int $exportacionId)
    {
        $this->exportacionId = $exportacionId;
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $exportacion = Exportacion::find($this->exportacionId);

        if (!$exportacion) {
            Log::error("GenerarPdfMasivoJob: exportacion #{$this->exportacionId} no encontrada.");
            return;
        }

        $exportacion->update(['estado' => 'PROCESANDO']);

        try {
            $ids     = $exportacion->ids;
            $total   = count($ids);
            $empresa = Empresa::instancia();

            // Crear ZIP temporal
            $nombreArchivo = 'facturas_' . now()->format('YmdHis') . '_' . $exportacion->id . '.zip';
            $rutaZip       = storage_path('app/public/exportaciones/' . $nombreArchivo);

            // Asegurar que el directorio existe
            if (!is_dir(storage_path('app/public/exportaciones'))) {
                mkdir(storage_path('app/public/exportaciones'), 0755, true);
            }

            $zip = new ZipArchive();
            if ($zip->open($rutaZip, ZipArchive::CREATE) !== true) {
                throw new \RuntimeException("No se pudo crear el archivo ZIP en: {$rutaZip}");
            }

            $procesados = 0;

            // Procesar en lotes de 10 para no agotar memoria
            $chunks = array_chunk($ids, 10);

            foreach ($chunks as $chunk) {
                $facturas = Factura::with(['cliente', 'pagos', 'tarifaPeriodo'])
                    ->whereIn('id', $chunk)
                    ->where('estado', '!=', 'ANULADA')
                    ->get();

                foreach ($facturas as $factura) {
                    try {
                        $facturaCol = collect([$factura]);
                        $pdf = PDF::loadView('facturacion.facturas.pdf', [
                            'facturas' => $facturaCol,
                            'empresa'  => $empresa,
                        ])->setPaper('letter', 'portrait');

                        $nombrePdf = "Factura_{$factura->numero_factura}_{$factura->suscriptor}.pdf";
                        $zip->addFromString($nombrePdf, $pdf->output());
                    } catch (\Throwable $e) {
                        Log::warning("PDF omitido para factura #{$factura->id}: " . $e->getMessage());
                    }

                    $procesados++;
                }

                // Actualizar progreso después de cada lote
                $exportacion->actualizarProgreso($procesados);
            }

            $zip->close();

            $exportacion->marcarListo('exportaciones/' . $nombreArchivo);

        } catch (\Throwable $e) {
            Log::error("GenerarPdfMasivoJob #{$this->exportacionId} falló: " . $e->getMessage());
            $exportacion->marcarError($e->getMessage());
        }
    }

    public function failed(\Throwable $exception): void
    {
        $exportacion = Exportacion::find($this->exportacionId);
        if ($exportacion) {
            $exportacion->marcarError('Job fallido: ' . $exception->getMessage());
        }
    }
}
