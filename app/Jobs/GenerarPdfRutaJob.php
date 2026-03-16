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

class GenerarPdfRutaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries   = 1;

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
            Log::error("GenerarPdfRutaJob: exportacion #{$this->exportacionId} no encontrada.");
            return;
        }

        $exportacion->update(['estado' => 'PROCESANDO']);

        try {
            $ids     = $exportacion->ids;
            $total   = count($ids);
            $empresa = Empresa::instancia();

            $ruta    = $exportacion->id_ruta;
            $periodo = $exportacion->periodo;

            $dir = storage_path('app/public/exportaciones');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $nombreArchivo = "facturas_ruta_{$ruta}_{$periodo}_" . $exportacion->id . '.zip';
            $rutaZip       = $dir . '/' . $nombreArchivo;

            $zip = new ZipArchive();
            if ($zip->open($rutaZip, ZipArchive::CREATE) !== true) {
                throw new \RuntimeException("No se pudo crear el ZIP en: {$rutaZip}");
            }

            $procesados = 0;

            foreach (array_chunk($ids, 10) as $chunk) {
                $facturas = Factura::with(['cliente', 'pagos', 'tarifaPeriodo'])
                    ->whereIn('id', $chunk)
                    ->get();

                foreach ($facturas as $factura) {
                    try {
                        $pdf = PDF::loadView('facturacion.facturas.pdf', [
                            'facturas' => collect([$factura]),
                            'empresa'  => $empresa,
                        ])->setPaper('letter', 'portrait');

                        $zip->addFromString(
                            "Factura_{$factura->numero_factura}_{$factura->suscriptor}.pdf",
                            $pdf->output()
                        );
                    } catch (\Throwable $e) {
                        Log::warning("PDF omitido factura #{$factura->id}: " . $e->getMessage());
                    }

                    $procesados++;
                }

                $exportacion->actualizarProgreso($procesados);
            }

            $zip->close();

            $exportacion->marcarListo('exportaciones/' . $nombreArchivo);

        } catch (\Throwable $e) {
            Log::error("GenerarPdfRutaJob #{$this->exportacionId} falló: " . $e->getMessage());
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
