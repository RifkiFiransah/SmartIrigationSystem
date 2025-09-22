<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\IrrigationDailyPlan;
use App\Models\IrrigationSession;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DeviceUsageController extends Controller
{
    /**
     * Sessions for a device (today or latest plan) with planned vs actual usage.
     */
    public function sessions(Device $device)
    {
        if(!$device){
            return response()->json(['message'=>'Device not found'], 404);
        }
        // Ambil plan hari ini (atau terakhir tersedia)
        $plan = IrrigationDailyPlan::with(['sessions' => function($q){
            $q->orderBy('session_index');
        }])->whereDate('plan_date', today())->first();

        if(!$plan){
            $plan = IrrigationDailyPlan::with('sessions')->latest('plan_date')->first();
        }

        if(!$plan){
            return response()->json([
                'device' => [
                    'id' => $device->id,
                    'device_id' => $device->device_id,
                    'device_name' => $device->device_name,
                ],
                'sessions' => [],
                'summary' => [
                    'total_planned_l' => 0,
                    'total_actual_l' => 0,
                    'efficiency_pct' => null,
                ],
                'plan_date' => null,
            ]);
        }

        $sessions = $plan->sessions->map(function(IrrigationSession $s){
            $planned = (float)($s->adjusted_volume_l ?? $s->planned_volume_l ?? 0);
            $actual = (float)($s->actual_volume_l ?? 0);
            $eff = $planned > 0 ? round(($actual / max($planned,0.0001)) * 100, 1) : null;
            // Frontend welcome.blade expects keys: index, time, planned_l, actual_l
            return [
                'index' => $s->session_index,
                'time' => $s->scheduled_time,
                'planned_l' => $planned,
                'actual_l' => $actual,
                'status' => $s->status,
                'efficiency_pct' => $eff,
                'started_at' => optional($s->started_at)->toDateTimeString(),
                'completed_at' => optional($s->completed_at)->toDateTimeString(),
            ];
        });

        $totalPlanned = $sessions->sum('planned_l');
        $totalActual = $sessions->sum('actual_l');
        $overallEff = $totalPlanned > 0 ? round(($totalActual / $totalPlanned)*100,1) : null;

        return response()->json([
            'device' => [
                'id' => $device->id,
                'device_id' => $device->device_id,
                'device_name' => $device->device_name,
            ],
            'plan_date' => $plan->plan_date->toDateString(),
            'sessions' => $sessions,
            'summary' => [
                'total_planned_l' => round($totalPlanned,2),
                'total_actual_l' => round($totalActual,2),
                'efficiency_pct' => $overallEff,
            ],
        ]);
    }

    /**
     * Usage history (aggregate daily water usage associated with this device via water_usage_logs) last N days.
     */
    public function usageHistory(Device $device, Request $request)
    {
        if(!$device){
            return response()->json(['message'=>'Device not found'], 404);
        }
        $days = (int)($request->query('days', 14));
        $days = min(max($days, 1), 60);

        $fromDate = now()->subDays($days - 1)->toDateString();

        $rows = DB::table('water_usage_logs')
            ->selectRaw('usage_date, SUM(volume_used_l) as total_l, COUNT(*) as entries')
            ->where('device_id', $device->id)
            ->where('usage_date', '>=', $fromDate)
            ->groupBy('usage_date')
            ->orderBy('usage_date')
            ->get();

        $history = $rows->map(fn($r)=>[
            'date' => $r->usage_date,
            'total_l' => (float) $r->total_l,
            'sessions' => (int) $r->entries,
        ]);

        return response()->json([
            'device' => [
                'id' => $device->id,
                'device_id' => $device->device_id,
                'device_name' => $device->device_name,
            ],
            'days' => $days,
            'history' => $history,
        ]);
    }
}
