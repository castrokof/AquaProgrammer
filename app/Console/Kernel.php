<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // ─── Verificación de fotos de clientes ──────────────────────────────
        // Verifica que las fotos de órdenes ejecutadas (Estado=4) existan en disco.
        // Procesa hasta 50 órdenes por ejecución; corre cada minuto.
        //
        // Cron en Hostinger cPanel (una sola línea):
        //   * * * * * /usr/bin/php artisan schedule:run >> /dev/null 2>&1
        //
        // Para ejecutar cada 30 s añade una segunda línea:
        //   * * * * * sleep 30 && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
        $schedule->command('fotos:verificar-cliente --lote=50')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->runInBackground();

        // ─── Procesamiento de cola de trabajos (Jobs) ────────────────────────
        // Comando que funciona en el servidor (ejecutar desde el directorio de la app):
        //   /usr/bin/php artisan queue:work database --tries=1 --timeout=290
        //
        // Nota: --once procesa un solo job; sin --once corre en modo daemon.
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
