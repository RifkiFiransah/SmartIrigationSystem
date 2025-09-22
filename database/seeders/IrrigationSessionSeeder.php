<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IrrigationSessionSeeder extends Seeder
{
    /**
     * Seed sample irrigation sessions for existing daily plans (or synthesize minimal plans if none).
     */
    public function run(): void
    {
        // $this->command->info('Seeding irrigation sessions...');

        // Get plan or create a single aggregate plan if none (schema is global per day in current migration)
        $plans = DB::table('irrigation_daily_plans')->where('plan_date', today()->toDateString())->get();
        if ($plans->isEmpty()) {
            $base = rand(80,160)/10; // 8.0 - 16.0 L total baseline for all sessions
            DB::table('irrigation_daily_plans')->insert([
                'plan_date' => today()->toDateString(),
                'base_total_volume_l' => $base,
                'adjusted_total_volume_l' => $base * (rand(95,105)/100),
                'adjustment_factors' => json_encode(['weather'=>'normal']),
                'status' => 'generated',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $plans = DB::table('irrigation_daily_plans')->where('plan_date', today()->toDateString())->get();
        }

        $rows = [];
        foreach ($plans as $plan) {
                // Skip if sessions already exist for this plan (idempotent rerun)
                $existingCount = DB::table('irrigation_sessions')->where('irrigation_daily_plan_id', $plan->id)->count();
                if ($existingCount > 0) {
                    $this->command?->info("Plan {$plan->id} already has {$existingCount} sessions - skipping.");
                    continue;
                }
            // 3 sessions per plan: pagi, siang, sore
            $baseTimes = ['06:30:00','12:30:00','17:30:00'];
            $sessionIndex = 1;
            $total = (float)($plan->adjusted_total_volume_l ?: $plan->base_total_volume_l);
            $remaining = $total;
            foreach ($baseTimes as $t) {
                $planned = round(max(0.3, $remaining/ (4 - $sessionIndex)),2); // simple distribution
                $adjusted = $planned + (rand(-5,5)/100); // minor adjustment
                $status = 'completed';
                $start = Carbon::parse($plan->plan_date.' '.$t);
                $durationMin = rand(5,15);
                $end = $start->copy()->addMinutes($durationMin);
                $actual = round($adjusted * (rand(90,105)/100),2);
                $remaining -= $planned;

                $rows[] = [
                    'irrigation_daily_plan_id' => $plan->id,
                    'session_index' => $sessionIndex,
                    'scheduled_time' => $t,
                    'planned_volume_l' => $planned,
                    'adjusted_volume_l' => $adjusted,
                    'actual_volume_l' => $actual,
                    'status' => $status,
                    'started_at' => $start,
                    'completed_at' => $end,
                    'meta' => json_encode([
                        'duration_min' => $durationMin,
                        'method' => 'drip',
                        'efficiency_pct' => rand(85,95),
                        'share_pct' => $total ? round($planned/$total*100,1) : null,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $sessionIndex++;
            }
        }

        if ($rows) {
            DB::table('irrigation_sessions')->insert($rows);
        }
        // $this->command->info('Created '.count($rows).' irrigation sessions.');
    }
}
