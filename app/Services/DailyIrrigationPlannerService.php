<?php

namespace App\Services;

use App\Models\IrrigationDailyPlan;
use App\Models\IrrigationSession;
use App\Models\WaterStorage;
use App\Models\SensorData;

class DailyIrrigationPlannerService
{
    public function generateTodayPlan(array $options = []): IrrigationDailyPlan
    {
        $date = today();
        $existing = IrrigationDailyPlan::whereDate('plan_date', $date)->first();
        if ($existing) {
            return $existing;
        }
        $storage = WaterStorage::first();
        $area = $storage?->area_size_sqm ?? 0;
        $baselinePerSqm = config('irrigation.baseline_liters_per_sqm', 3.5);
        $baseTotal = round($area * $baselinePerSqm, 2);

        $latestSensors = SensorData::latest('recorded_at')->take(10)->get();
        $avgTemp = (float) round($latestSensors->avg('temperature_c') ?? 30, 2);
        $avgSoil = (float) round($latestSensors->avg('soil_moisture_pct') ?? 40, 2);
        $avgWind = (float) round($latestSensors->avg('wind_speed_ms') ?? 0, 2);

        $factors = [
            'base_total_l' => $baseTotal,
            'temperature_c' => $avgTemp,
            'soil_moisture_pct' => $avgSoil,
            'wind_speed_ms' => $avgWind,
        ];

        $multiplier = 1.0;
        if ($avgTemp > 32) {
            $multiplier += min((($avgTemp - 32) / 2) * 0.10, 0.30);
        } elseif ($avgTemp < 20) {
            $multiplier -= 0.10;
        }
        $factors['temp_multiplier'] = round($multiplier, 3);

        if ($avgSoil > 60) {
            $multiplier -= 0.20;
        } elseif ($avgSoil < 30) {
            $multiplier += 0.15;
        }
        $factors['soil_multiplier'] = round($multiplier, 3);

        if ($avgWind > 3) {
            $multiplier += min((($avgWind - 3) / 2) * 0.05, 0.20);
        }
        $factors['wind_multiplier'] = round($multiplier, 3);

        $multiplier = max(0.5, min($multiplier, 1.6));
        $factors['final_multiplier'] = $multiplier;

        $adjustedTotal = round($baseTotal * $multiplier, 2);
        if ($storage && $adjustedTotal > $storage->current_volume_liters) {
            $factors['capped_by_storage'] = true;
            $adjustedTotal = round($storage->current_volume_liters * 0.9, 2);
        }

        $plan = IrrigationDailyPlan::create([
            'plan_date' => $date,
            'base_total_volume_l' => $baseTotal,
            'adjusted_total_volume_l' => $adjustedTotal,
            'adjustment_factors' => $factors,
            'status' => 'planned',
        ]);

        $times = config('irrigation.session_times', ['06:00','12:00','17:30']);
        $weights = config('irrigation.session_distribution', [0.4,0.3,0.3]);
        if (array_sum($weights) != 1) $weights = [0.34,0.33,0.33];
        foreach ([1,2,3] as $i) {
            $plannedVol = round($adjustedTotal * $weights[$i-1], 2);
            IrrigationSession::create([
                'irrigation_daily_plan_id' => $plan->id,
                'session_index' => $i,
                'scheduled_time' => $times[$i-1] ?? '00:00',
                'planned_volume_l' => $plannedVol,
                'adjusted_volume_l' => $plannedVol,
                'status' => 'pending',
                'meta' => ['weight' => $weights[$i-1]],
            ]);
        }
        return $plan->fresh('sessions');
    }
}
