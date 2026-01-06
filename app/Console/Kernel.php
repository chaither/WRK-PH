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
        // This schedule will now be dynamically determined by the PayrollScheduleController
        $schedule->command('payroll:generate')->dailyAt('23:00'); // Run daily to check for schedules
        
        // Automatically sync biometric attendances to DTR records every 10 seconds
        // This runs as a backup - the main sync happens via middleware on each request
        // This ensures that when employees fingerprint, their clock in/out is recorded immediately
        $schedule->command('zkteco:sync --attendances')
                 ->everyMinute() // Laravel scheduler minimum is 1 minute
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/zkteco-sync.log'));
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
