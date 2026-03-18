<?php

namespace App\Console\Commands;

use App\Models\Admin\Ordenesmtl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Verifica que las fotos de las órdenes ejecutadas existan en disco.
 *
 * Valores de foto_verificada:
 *   0  → pendiente (aún no se ha verificado)
 *   1  → OK  (archivo existe en disco)
 *  -1  → faltante (el archivo no existe en disco)
 *
 * Para ejecutar cada 30 segundos en cron, añade DOS líneas:
 *   * * * * * /usr/bin/php /ruta/artisan schedule:run >> /dev/null 2>&1
 *   * * * * * sleep 30 && /usr/bin/php /ruta/artisan schedule:run >> /dev/null 2>&1
 */
class VerificarFotosClienteCommand extends Command
{
    protected $signature = 'fotos:verificar-cliente
                            {--lote=50 : Cantidad máxima de órdenes a procesar por ejecución}';

    protected $description = 'Verifica que las fotos de órdenes ejecutadas existan en disco (lote por ejecución)';

    public function handle(): int
    {
        $lote = (int) $this->option('lote');

        // Órdenes ejecutadas (Estado=4) con foto1 pendiente de verificar
        $ordenes = Ordenesmtl::where('Estado', 4)
            ->whereNotNull('foto1')
            ->where('foto1', '!=', '')
            ->where('foto_verificada', 0)
            ->limit($lote)
            ->get(['id', 'Suscriptor', 'foto1', 'foto2']);

        if ($ordenes->isEmpty()) {
            $this->info('No hay fotos pendientes de verificar.');
            return 0;
        }

        $ok       = 0;
        $faltante = 0;

        foreach ($ordenes as $orden) {
            $foto1Existe = $this->archivoExiste($orden->foto1);
            $foto2Existe = empty($orden->foto2) || $this->archivoExiste($orden->foto2);

            if ($foto1Existe && $foto2Existe) {
                $estado = 1;   // OK
                $ok++;
            } else {
                $estado = -1;  // Faltante
                $faltante++;

                Log::warning('Foto faltante en orden', [
                    'orden_id'   => $orden->id,
                    'suscriptor' => $orden->Suscriptor,
                    'foto1'      => $orden->foto1,
                    'foto2'      => $orden->foto2,
                    'foto1_ok'   => $foto1Existe,
                    'foto2_ok'   => $foto2Existe,
                ]);
            }

            $orden->update([
                'foto_verificada'    => $estado,
                'foto_verificada_at' => now(),
            ]);
        }

        $this->info("Verificadas: {$ordenes->count()} | OK: {$ok} | Faltantes: {$faltante}");

        return 0;
    }

    /**
     * Comprueba si el archivo existe en la ruta pública o absoluta.
     */
    private function archivoExiste(string $ruta): bool
    {
        // Ruta relativa guardada como "imageneslectura/12345_1.jpg"
        if (file_exists(public_path($ruta))) {
            return true;
        }

        // Ruta absoluta (por si acaso)
        if (file_exists($ruta)) {
            return true;
        }

        return false;
    }
}
