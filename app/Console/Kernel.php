<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Run every minute to honor minute-level opens and closes
        $schedule->command('valves:run-schedules')->everyMinute();
    // Generate irrigation plan daily just after midnight
    $schedule->command('irrigation:plan:today')->dailyAt('00:05');
    // Check and run due irrigation sessions
    $schedule->command('irrigation:run:due')->everyMinute();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
