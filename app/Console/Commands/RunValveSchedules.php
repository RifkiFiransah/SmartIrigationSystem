<?php

namespace App\Console\Commands;

use App\Models\IrrigationValve;
use App\Models\IrrigationValveSchedule;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RunValveSchedules extends Command
{
    protected $signature = 'valves:run-schedules';
    protected $description = 'Run minimal irrigation valve schedules (open/close based on simple daily/weekly times).';

    public function handle(): int
    {
        $now = now();
        $dow = $now->dayOfWeek; // 0=Sun ... 6=Sat

        $schedules = IrrigationValveSchedule::query()
            ->where('is_active', true)
            ->get();

        foreach ($schedules as $schedule) {
            $days = $schedule->days_of_week ?: null;
            if (is_array($days) && !in_array($dow, $days)) {
                continue; // not today
            }

            // Determine today's start time
            $start = $now->copy()->setTimeFromTimeString($schedule->start_time);
            $endOpenWindow = $start->copy()->addMinute();

            // Fetch valve by node
            $valve = IrrigationValve::where('node_uid', $schedule->node_uid)->first();
            if (!$valve || !$valve->is_active) {
                continue;
            }

            // Close valve if duration elapsed
            if ($valve->isOpen() && $valve->last_open_at) {
                if ($valve->last_open_at->addMinutes($schedule->duration_minutes)->lte($now)) {
                    $valve->close();
                    $this->info("Closed valve {$valve->node_uid} after scheduled duration");
                }
            }

            // Open at start time (once per day)
            $alreadyRunForToday = $schedule->last_run_at && $schedule->last_run_at->gte($start);
            if (!$alreadyRunForToday && $now->betweenIncluded($start, $endOpenWindow)) {
                $valve->open($schedule->duration_minutes);
                $schedule->last_run_at = $now;
                $schedule->save();
                $this->info("Opened valve {$valve->node_uid} per schedule");
            }
        }

        return self::SUCCESS;
    }
}
