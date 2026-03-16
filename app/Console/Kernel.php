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
        // Cron configurado en Hostinger cPanel:
        // * * * * * /usr/bin/php /home/u359728731/domains/manteliviano.com/AquaProgrammerData/artisan queue:work database --once --tries=1 --timeout=290 >> /dev/null 2>&1
        $schedule->command('queue:work database --once --tries=1 --timeout=290')
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
