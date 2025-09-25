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

    // Auto mark devices offline if no heartbeat in last 10 minutes (only for auto mode)
    $schedule->call(function(){
        \App\Models\Device::where('connection_state_source','auto')
            ->where('connection_state','online')
            ->where(function($q){
                $q->whereNull('last_seen_at')->orWhere('last_seen_at','<', \Carbon\Carbon::now()->subMinutes(10));
            })
            ->update(['connection_state' => 'offline']);
    })->everyFiveMinutes()->name('devices:auto-offline');

    // Send daily system report email at 07:00
    $schedule->command('system:report-email')->dailyAt('07:00')->name('daily-system-report');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
