<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\IrrigationSession;
use App\Models\IrrigationDailyPlan;
use App\Models\WaterStorage;

class RunDueIrrigationSessions extends Command
{
    protected $signature = 'irrigation:run:due';
    protected $description = 'Execute due irrigation sessions (simulation)';

    public function handle(): int
    {
        $now = now();
        $sessions = IrrigationSession::where('status','pending')
            ->whereHas('plan', fn($q)=>$q->whereDate('plan_date', today()))
            ->where('scheduled_time','<=',$now->format('H:i:00'))
            ->orderBy('scheduled_time')
            ->get();
        if ($sessions->isEmpty()) {
            $this->line('No due sessions.');
            return self::SUCCESS;
        }
        $storage = WaterStorage::first();
        foreach ($sessions as $session) {
            if (!$storage) {
                $session->status='skipped';
                $session->meta=['reason'=>'no_storage'];
                $session->save();
                continue;
            }
            $session->status='running';
            $session->started_at=now();
            $session->save();
            $target = (float)($session->adjusted_volume_l ?? $session->planned_volume_l);
            $available = (float)$storage->current_volume_liters;
            $actual = min($target,$available);
            $storage->deductForIrrigation($actual,[
                'session_index'=>$session->session_index,
                'scheduled_time'=>$session->scheduled_time,
            ]);
            $session->actual_volume_l = $actual;
            $session->completed_at = now();
            $session->status='completed';
            $session->save();
        }
        $plan = IrrigationDailyPlan::whereDate('plan_date', today())->first();
        if ($plan) {
            $all = $plan->sessions;
            if ($all->every(fn($s)=>$s->status==='completed')) $plan->status='completed';
            elseif ($all->contains(fn($s)=>$s->status==='completed')) $plan->status='partial';
            $plan->save();
        }
        $this->info('Processed '.$sessions->count().' sessions.');
        return self::SUCCESS;
    }
}
