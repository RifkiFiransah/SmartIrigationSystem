<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DailyIrrigationPlannerService;
use App\Models\IrrigationDailyPlan;

class GenerateIrrigationPlan extends Command
{
    protected $signature = 'irrigation:plan:today {--force}';
    protected $description = 'Generate today\'s irrigation plan (3 sessions)';

    public function handle(DailyIrrigationPlannerService $planner): int
    {
        $exists = IrrigationDailyPlan::whereDate('plan_date', today())->first();
        if ($exists && !$this->option('force')) {
            $this->info('Plan already exists. Use --force to regenerate.');
            return self::SUCCESS;
        }
        if ($exists) {
            $exists->sessions()->delete();
            $exists->delete();
        }
        $plan = $planner->generateTodayPlan();
        $this->info('Generated plan total '.$plan->adjusted_total_volume_l.' L');
        return self::SUCCESS;
    }
}
