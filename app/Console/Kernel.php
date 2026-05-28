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
        \App\Console\Commands\CloseExpiredSessions::class,
        \App\Console\Commands\StartActiveSessions::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Auto-start sesi yang sudah mencapai waktu_mulai
        $schedule->command('sesi:start-active')->everyMinute();
        // Auto-close sesi yang sudah melewati waktu_selesai
        $schedule->command('sesi:close-expired')->everyMinute();
        // Proses semua job queue (auto-save jawaban, pelanggaran, dll)
        $schedule->command('queue:work --stop-when-empty --sleep=1 --tries=3 --timeout=120')
            ->everyThirtySeconds()->withoutOverlapping()->runInBackground();
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