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
        // Procesa un job por minuto desde la base de datos.
        // Cron configurado en Hostinger cPanel (usar schedule:run):
        // * * * * * /usr/bin/php /home/u359728731/domains/manteliviano.com/AquaProgrammerData/artisan schedule:run >> /dev/null 2>&1
        $schedule->command('queue:work --queue=default --once --stop-when-empty --timeout=3000 --tries=1')
                 ->everyMinute()
                 ->withoutOverlapping(3);

        // Elimina ZIPs de exportaciones con más de 7 días (se ejecuta diariamente a medianoche)
        $schedule->command('exportaciones:limpiar')
                 ->dailyAt('00:00')
                 ->withoutOverlapping();
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
