<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        //backup otomatis 6 bulan sekali
        $schedule->command('backup:simple')
            -> cron(1, 7, '02:00')
            -> withoutOverlapping()
            -> onFailure(function () {
                //log gagal backup
                Log::error('Backup gagal dijalankan pada: ' . now());
            })
            -> onSuccess(function() {
                //log berhasil backup
                Log::info('Backup berhasil dijalankan pada: ' . now());
            });

        $schedule->command('backup:simple')
            -> weeklyOn(0, '03:00') // Setiap hari Minggu pukul 03:00
            -> sundays()
            -> at('03:00')
            -> withoutOverlapping();

        $schedule->command('backup:clean')
            -> daily()
            -> at('04:00');

        $schedule->command('backup:monitor')
            -> daily()
            -> at('05:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
