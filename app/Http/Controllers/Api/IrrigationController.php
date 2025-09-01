<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IrrigationControl;
use App\Models\IrrigationSchedule;
use App\Models\IrrigationLog;
use App\Models\SensorData;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class IrrigationController extends Controller
{
    /**
     * Get semua irrigation controls
     * GET /api/irrigation/controls
     */
    public function getControls(): JsonResponse
    {
        try {
            $controls = IrrigationControl::with(['device', 'schedules'])
                ->active()
                ->get()
                ->map(function ($control) {
                    return [
                        'id' => $control->id,
                        'control_name' => $control->control_name,
                        'control_type' => $control->control_type,
                        'device' => [
                            'id' => $control->device->id,
                            'device_name' => $control->device->device_name,
                            'device_id' => $control->device->device_id,
                        ],
                        'pin_number' => $control->pin_number,
                        'status' => $control->status,
                        'status_icon' => $control->status_icon,
                        'mode' => $control->mode,
                        'duration_minutes' => $control->duration_minutes,
                        'is_running' => $control->isRunning(),
                        'is_auto_mode' => $control->isAutoMode(),
                        'last_activated_at' => $control->last_activated_at,
                        'today_duration' => $control->today_duration,
                        'active_schedules' => $control->schedules()
                            ->active()
                            ->count(),
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Irrigation controls retrieved successfully',
                'data' => $controls
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve irrigation controls',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kontrol manual - Start irrigation
     * POST /api/irrigation/start
     */
    public function startIrrigation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'control_id' => 'required|integer|exists:irrigation_controls,id',
            'duration_minutes' => 'nullable|integer|min:1|max:1440', // max 24 jam
            'trigger_type' => 'sometimes|in:manual,api,emergency',
            'triggered_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $control = IrrigationControl::findOrFail($request->control_id);

            // Check apakah sudah berjalan
            if ($control->isRunning()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Irrigation control is already running',
                    'data' => [
                        'control_id' => $control->id,
                        'status' => $control->status
                    ]
                ], 400);
            }

            // Update control status
            $control->update([
                'status' => 'on',
                'mode' => 'manual',
                'duration_minutes' => $request->duration_minutes ?? $control->duration_minutes,
                'last_activated_at' => now(),
            ]);

            // Buat log
            $log = IrrigationLog::create([
                'irrigation_control_id' => $control->id,
                'irrigation_schedule_id' => null,
                'action' => 'start',
                'trigger_type' => $request->trigger_type ?? 'manual',
                'triggered_by' => $request->triggered_by ?? 'API',
                'started_at' => now(),
                'status' => 'running',
                'notes' => $request->notes,
                'sensor_data_snapshot' => $this->getCurrentSensorSnapshot($control->device_id),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Irrigation started successfully',
                'data' => [
                    'control_id' => $control->id,
                    'control_name' => $control->control_name,
                    'status' => $control->status,
                    'duration_minutes' => $control->duration_minutes,
                    'started_at' => $log->started_at,
                    'log_id' => $log->id,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to start irrigation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kontrol manual - Stop irrigation
     * POST /api/irrigation/stop
     */
    public function stopIrrigation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'control_id' => 'required|integer|exists:irrigation_controls,id',
            'reason' => 'nullable|string|max:500',
            'triggered_by' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $control = IrrigationControl::findOrFail($request->control_id);

            // Check apakah sedang berjalan
            if (!$control->isRunning()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Irrigation control is not running',
                    'data' => [
                        'control_id' => $control->id,
                        'status' => $control->status
                    ]
                ], 400);
            }

            // Update control status
            $control->update([
                'status' => 'off',
                'last_deactivated_at' => now(),
            ]);

            // Update log yang sedang berjalan
            $runningLog = IrrigationLog::where('irrigation_control_id', $control->id)
                ->where('status', 'running')
                ->latest()
                ->first();

            if ($runningLog) {
                $runningLog->markAsCompleted($request->reason);
                $runningLog->calculateWaterUsage();
            }

            // Buat log stop
            IrrigationLog::create([
                'irrigation_control_id' => $control->id,
                'action' => 'stop',
                'trigger_type' => 'manual',
                'triggered_by' => $request->triggered_by ?? 'API',
                'started_at' => now(),
                'ended_at' => now(),
                'duration_seconds' => 0,
                'status' => 'completed',
                'notes' => $request->reason,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Irrigation stopped successfully',
                'data' => [
                    'control_id' => $control->id,
                    'control_name' => $control->control_name,
                    'status' => $control->status,
                    'stopped_at' => now(),
                    'total_duration' => $runningLog ? $runningLog->formatted_duration : null,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to stop irrigation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle mode auto/manual
     * POST /api/irrigation/toggle-mode
     */
    public function toggleMode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'control_id' => 'required|integer|exists:irrigation_controls,id',
            'mode' => 'required|in:auto,manual',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $control = IrrigationControl::findOrFail($request->control_id);
            
            $oldMode = $control->mode;
            $control->update(['mode' => $request->mode]);

            // Log perubahan mode
            IrrigationLog::create([
                'irrigation_control_id' => $control->id,
                'action' => 'manual_override',
                'trigger_type' => 'manual',
                'triggered_by' => 'API',
                'started_at' => now(),
                'ended_at' => now(),
                'status' => 'completed',
                'notes' => "Mode changed from {$oldMode} to {$request->mode}",
            ]);

            return response()->json([
                'success' => true,
                'message' => "Mode changed to {$request->mode}",
                'data' => [
                    'control_id' => $control->id,
                    'old_mode' => $oldMode,
                    'new_mode' => $control->mode,
                    'is_auto_mode' => $control->isAutoMode(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change mode',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get irrigation status dan statistik
     * GET /api/irrigation/status
     */
    public function getStatus(): JsonResponse
    {
        try {
            $totalControls = IrrigationControl::active()->count();
            $runningControls = IrrigationControl::running()->count();
            $autoModeControls = IrrigationControl::autoMode()->count();

            $todayLogs = IrrigationLog::today()
                ->where('action', 'start')
                ->with('irrigationControl')
                ->get();

            $todayStats = [
                'total_runs' => $todayLogs->count(),
                'total_duration_minutes' => IrrigationLog::today()
                    ->where('status', 'completed')
                    ->sum('duration_seconds') / 60,
                'total_water_used' => IrrigationLog::today()
                    ->where('status', 'completed')
                    ->sum('total_water_used') ?? 0,
            ];

            $runningNow = IrrigationControl::with(['device', 'logs' => function($query) {
                $query->running()->latest();
            }])
                ->running()
                ->get()
                ->map(function($control) {
                    $runningLog = $control->logs->first();
                    return [
                        'control_id' => $control->id,
                        'control_name' => $control->control_name,
                        'device_name' => $control->device->device_name,
                        'started_at' => $runningLog?->started_at,
                        'duration_so_far' => $runningLog ? now()->diffInMinutes($runningLog->started_at) : 0,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Irrigation status retrieved successfully',
                'data' => [
                    'system_overview' => [
                        'total_controls' => $totalControls,
                        'running_controls' => $runningControls,
                        'auto_mode_controls' => $autoModeControls,
                        'manual_mode_controls' => $totalControls - $autoModeControls,
                    ],
                    'today_stats' => $todayStats,
                    'running_now' => $runningNow,
                    'server_time' => now()->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve irrigation status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get irrigation logs
     * GET /api/irrigation/logs
     */
    public function getLogs(Request $request): JsonResponse
    {
        try {
            $query = IrrigationLog::with(['irrigationControl.device', 'irrigationSchedule'])
                ->orderBy('created_at', 'desc');

            // Filter berdasarkan parameter
            if ($request->control_id) {
                $query->where('irrigation_control_id', $request->control_id);
            }

            if ($request->action) {
                $query->where('action', $request->action);
            }

            if ($request->trigger_type) {
                $query->where('trigger_type', $request->trigger_type);
            }

            if ($request->date) {
                $query->whereDate('started_at', $request->date);
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            $logs = $query->paginate($request->per_page ?? 20);

            $logs->getCollection()->transform(function($log) {
                return [
                    'id' => $log->id,
                    'control_name' => $log->irrigationControl->control_name,
                    'device_name' => $log->irrigationControl->device->device_name,
                    'action' => $log->action,
                    'action_icon' => $log->action_icon,
                    'trigger_type' => $log->trigger_type,
                    'triggered_by' => $log->triggered_by,
                    'started_at' => $log->started_at,
                    'ended_at' => $log->ended_at,
                    'duration' => $log->formatted_duration,
                    'status' => $log->status,
                    'status_icon' => $log->status_icon,
                    'water_used' => $log->total_water_used,
                    'notes' => $log->notes,
                    'error_message' => $log->error_message,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Irrigation logs retrieved successfully',
                'data' => $logs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve irrigation logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Automation - Check dan jalankan scheduled irrigation
     * POST /api/irrigation/run-scheduled
     */
    public function runScheduled(): JsonResponse
    {
        try {
            $dueSchedules = IrrigationSchedule::due()
                ->with('irrigationControl')
                ->get();

            $executed = [];
            $errors = [];

            foreach ($dueSchedules as $schedule) {
                try {
                    // Skip jika control sedang berjalan
                    if ($schedule->irrigationControl->isRunning()) {
                        continue;
                    }

                    // Check kondisi sensor jika sensor-based
                    if ($schedule->schedule_type === 'sensor_based') {
                        if (!$this->checkSensorConditions($schedule)) {
                            continue;
                        }
                    }

                    DB::beginTransaction();

                    // Start irrigation
                    $schedule->irrigationControl->update([
                        'status' => 'on',
                        'mode' => 'auto',
                        'duration_minutes' => $schedule->duration_minutes,
                        'last_activated_at' => now(),
                    ]);

                    // Buat log
                    $log = IrrigationLog::create([
                        'irrigation_control_id' => $schedule->irrigation_control_id,
                        'irrigation_schedule_id' => $schedule->id,
                        'action' => 'start',
                        'trigger_type' => 'schedule',
                        'triggered_by' => 'System Scheduler',
                        'started_at' => now(),
                        'status' => 'running',
                        'notes' => "Auto-started by schedule: {$schedule->schedule_name}",
                        'sensor_data_snapshot' => $this->getCurrentSensorSnapshot($schedule->irrigationControl->device_id),
                    ]);

                    // Update schedule
                    $schedule->markAsRun();

                    DB::commit();

                    $executed[] = [
                        'schedule_id' => $schedule->id,
                        'schedule_name' => $schedule->schedule_name,
                        'control_name' => $schedule->irrigationControl->control_name,
                        'started_at' => $log->started_at,
                        'duration_minutes' => $schedule->duration_minutes,
                    ];

                } catch (\Exception $e) {
                    DB::rollback();
                    $errors[] = [
                        'schedule_id' => $schedule->id,
                        'schedule_name' => $schedule->schedule_name,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($executed) > 0 ? 'Scheduled irrigation executed' : 'No scheduled irrigation due',
                'data' => [
                    'executed_count' => count($executed),
                    'error_count' => count($errors),
                    'executed' => $executed,
                    'errors' => $errors,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to run scheduled irrigation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Emergency stop semua irrigation
     * POST /api/irrigation/emergency-stop
     */
    public function emergencyStop(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $runningControls = IrrigationControl::running()->get();
            $stopped = [];

            foreach ($runningControls as $control) {
                // Stop control
                $control->update([
                    'status' => 'off',
                    'last_deactivated_at' => now(),
                ]);

                // Complete running logs
                $runningLog = IrrigationLog::where('irrigation_control_id', $control->id)
                    ->where('status', 'running')
                    ->latest()
                    ->first();

                if ($runningLog) {
                    $runningLog->markAsCancelled('Emergency stop');
                }

                // Log emergency stop
                IrrigationLog::create([
                    'irrigation_control_id' => $control->id,
                    'action' => 'stop',
                    'trigger_type' => 'emergency',
                    'triggered_by' => $request->triggered_by ?? 'Emergency API',
                    'started_at' => now(),
                    'ended_at' => now(),
                    'status' => 'completed',
                    'notes' => 'Emergency stop: ' . ($request->reason ?? 'No reason provided'),
                ]);

                $stopped[] = [
                    'control_id' => $control->id,
                    'control_name' => $control->control_name,
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Emergency stop executed successfully',
                'data' => [
                    'stopped_count' => count($stopped),
                    'stopped_controls' => $stopped,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to execute emergency stop',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Get current sensor data snapshot
     */
    protected function getCurrentSensorSnapshot($deviceId): ?array
    {
        try {
            $latestSensor = SensorData::where('device_id', $deviceId)
                ->latest()
                ->first();

            if (!$latestSensor) {
                return null;
            }

            return [
                'temperature' => $latestSensor->temperature,
                'humidity' => $latestSensor->humidity,
                'soil_moisture' => $latestSensor->soil_moisture,
                'water_flow' => $latestSensor->water_flow,
                'recorded_at' => $latestSensor->created_at,
            ];

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Helper: Check sensor conditions untuk schedule
     */
    protected function checkSensorConditions(IrrigationSchedule $schedule): bool
    {
        if (!$schedule->trigger_conditions) {
            return true;
        }

        try {
            $conditions = $schedule->trigger_conditions;
            $sensorData = $this->getCurrentSensorSnapshot($schedule->irrigationControl->device_id);

            if (!$sensorData) {
                return false;
            }

            // Check setiap kondisi
            foreach ($conditions as $field => $condition) {
                if (!isset($sensorData[$field])) {
                    continue;
                }

                $value = $sensorData[$field];
                
                // Contoh kondisi: {'soil_moisture': {'operator': '<', 'value': 30}}
                if (isset($condition['operator']) && isset($condition['value'])) {
                    switch ($condition['operator']) {
                        case '<':
                            if (!($value < $condition['value'])) return false;
                            break;
                        case '<=':
                            if (!($value <= $condition['value'])) return false;
                            break;
                        case '>':
                            if (!($value > $condition['value'])) return false;
                            break;
                        case '>=':
                            if (!($value >= $condition['value'])) return false;
                            break;
                        case '=':
                        case '==':
                            if (!($value == $condition['value'])) return false;
                            break;
                    }
                }
            }

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }
}
