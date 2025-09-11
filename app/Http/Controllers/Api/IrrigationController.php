<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IrrigationDailyPlan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class IrrigationController extends Controller
{
    /**
     * Get today's irrigation plan
     */
    public function todayPlan(): JsonResponse
    {
        try {
            $today = Carbon::today();
            
            $plan = IrrigationDailyPlan::with(['sessions' => function ($query) {
                $query->orderBy('session_index');
            }])
            ->where('plan_date', $today)
            ->first();

            if (!$plan) {
                // Create a sample plan if none exists
                $plan = $this->createSamplePlan($today);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatPlanResponse($plan)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch irrigation plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a sample irrigation plan for today
     */
    private function createSamplePlan($date)
    {
        $plan = IrrigationDailyPlan::create([
            'plan_date' => $date,
            'base_total_volume_l' => 150.0,
            'adjusted_total_volume_l' => 165.0,
            'adjustment_factors' => [
                'weather' => 1.05,
                'soil_moisture' => 1.05,
                'temperature' => 1.0
            ],
            'status' => 'active'
        ]);

        // Create sample sessions
        $sessions = [
            [
                'session_index' => 1,
                'scheduled_time' => '06:00:00',
                'planned_volume_l' => 55.0,
                'adjusted_volume_l' => 60.0,
                'status' => 'completed',
                'actual_volume_l' => 58.5,
                'started_at' => $date->copy()->setTime(6, 0),
                'completed_at' => $date->copy()->setTime(6, 15)
            ],
            [
                'session_index' => 2,
                'scheduled_time' => '12:00:00',
                'planned_volume_l' => 50.0,
                'adjusted_volume_l' => 55.0,
                'status' => 'pending',
                'actual_volume_l' => null,
                'started_at' => null,
                'completed_at' => null
            ],
            [
                'session_index' => 3,
                'scheduled_time' => '18:00:00',
                'planned_volume_l' => 45.0,
                'adjusted_volume_l' => 50.0,
                'status' => 'pending',
                'actual_volume_l' => null,
                'started_at' => null,
                'completed_at' => null
            ]
        ];

        foreach ($sessions as $sessionData) {
            $plan->sessions()->create($sessionData);
        }

        return $plan->load('sessions');
    }

    /**
     * Format the plan response for frontend
     */
    private function formatPlanResponse($plan)
    {
        $sessions = $plan->sessions->map(function ($session) {
            return [
                'index' => $session->session_index,
                'time' => substr($session->scheduled_time, 0, 5), // HH:MM format
                'planned_l' => (float) $session->planned_volume_l,
                'adjusted_l' => (float) $session->adjusted_volume_l,
                'actual_l' => $session->actual_volume_l ? (float) $session->actual_volume_l : null,
                'status' => $session->status
            ];
        });

        return [
            'id' => $plan->id,
            'plan_date' => $plan->plan_date->format('Y-m-d'),
            'base_total_l' => (float) $plan->base_total_volume_l,
            'adjusted_total_l' => (float) $plan->adjusted_total_volume_l,
            'completed_volume_l' => $plan->completed_volume,
            'status' => $plan->status,
            'adjustment_factors' => $plan->adjustment_factors,
            'sessions' => $sessions
        ];
    }
}
