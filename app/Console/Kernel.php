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
        // Verifica fotos de órdenes ejecutadas cada vez que se llama schedule:run.
        // Para que corra cada 30 s, añade DOS líneas en el cron del servidor:
        //
        //   * * * * * /usr/bin/php /ruta/artisan schedule:run >> /dev/null 2>&1
        //   * * * * * sleep 30 && /usr/bin/php /ruta/artisan schedule:run >> /dev/null 2>&1
        //
        // Así schedule:run se invoca a los :00 y a los :30 de cada minuto,
        // y este comando procesa hasta 50 fotos por invocación (≈ 30 s máx).
        $schedule->command('fotos:verificar-cliente --lote=50')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->runInBackground();
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
