<?php

namespace App\Console\Commands;

use App\Models\Exportacion;
use Illuminate\Console\Command;

class LimpiarExportaciones extends Command
{
    protected $signature   = 'exportaciones:limpiar';
    protected $description = 'Elimina los ZIPs de exportaciones con más de 7 días para liberar espacio';

    public function handle(): int
    {
        $limite = now()->subDays(7);

        $exportaciones = Exportacion::where('estado', 'LISTO')
            ->whereNotNull('archivo')
            ->where('updated_at', '<', $limite)
            ->get();

        $eliminados = 0;

        foreach ($exportaciones as $exp) {
            $fullPath = storage_path('app/public/' . $exp->archivo);

            if (file_exists($fullPath)) {
                @unlink($fullPath);
                $eliminados++;
            }

            // Marcar el archivo como nulo para que la UI muestre "Expirado"
            $exp->update(['archivo' => null]);
        }

        $this->info("Limpieza completada: {$eliminados} ZIP(s) eliminado(s).");

        return 0;
    }
}
