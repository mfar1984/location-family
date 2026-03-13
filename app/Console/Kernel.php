<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Refresh test data every 3 minutes for demo purposes
        // Remove this in production when real devices are sending data
        $schedule->command('test:refresh-data')
                 ->everyThreeMinutes()
                 ->withoutOverlapping()
                 ->runInBackground();
        
        // Cleanup inactive devices daily at 2:00 AM
        // Devices that haven't sent pings for 7 days will be marked as inactive
        $schedule->command('devices:cleanup-inactive --days=7')
                 ->dailyAt('02:00')
                 ->withoutOverlapping();
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