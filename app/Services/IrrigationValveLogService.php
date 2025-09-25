<?php

namespace App\Services;

use App\Models\Device;
use App\Models\IrrigationValve;
use App\Models\IrrigationValveLog;
use App\Models\IrrigationValveSchedule;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class IrrigationValveLogService
{
    /**
     * Log device connection status changes
     */
    public function logDeviceConnection(Device $device, string $action, string $trigger = 'system', array $metadata = []): IrrigationValveLog
    {
        if (!in_array($action, ['device_connect', 'device_disconnect'])) {
            throw new \InvalidArgumentException("Invalid device connection action: {$action}");
        }

        $irrigationValve = $device->irrigationValves()->first();
        
        return IrrigationValveLog::create([
            'irrigation_valve_id' => $irrigationValve?->id,
            'device_id' => $device->id,
            'node_uid' => $irrigationValve?->node_uid,
            'action' => $action,
            'trigger' => $trigger,
            'notes' => $this->generateDeviceConnectionNote($device, $action),
            'metadata' => array_merge([
                'device_name' => $device->device_name,
                'location' => $device->location,
                'previous_state' => $device->connection_state,
                'timestamp' => now(),
            ], $metadata),
            'user_id' => Auth::id(),
            'source_ip' => Request::ip(),
        ]);
    }

    /**
     * Log valve control actions (open/close/toggle)
     */
    public function logValveControl(IrrigationValve $valve, string $action, string $trigger = 'manual', ?int $durationSeconds = null, array $metadata = []): IrrigationValveLog
    {
        if (!in_array($action, ['open', 'close', 'toggle_mode', 'system_auto_open', 'system_auto_close'])) {
            throw new \InvalidArgumentException("Invalid valve control action: {$action}");
        }

        return IrrigationValveLog::create([
            'irrigation_valve_id' => $valve->id,
            'device_id' => $valve->device_id,
            'node_uid' => $valve->node_uid,
            'action' => $action,
            'trigger' => $trigger,
            'duration_seconds' => $durationSeconds,
            'notes' => $this->generateValveControlNote($valve, $action),
            'metadata' => array_merge([
                'device_name' => $valve->device?->device_name,
                'valve_description' => $valve->description,
                'gpio_pin' => $valve->gpio_pin,
                'previous_status' => $valve->status,
                'timestamp' => now(),
            ], $metadata),
            'user_id' => Auth::id(),
            'source_ip' => Request::ip(),
        ]);
    }

    /**
     * Log schedule-related actions
     */
    public function logScheduleAction(string $action, string $trigger = 'admin_panel', ?IrrigationValveSchedule $schedule = null, ?IrrigationValve $valve = null, array $metadata = []): IrrigationValveLog
    {
        if (!in_array($action, ['schedule_create', 'schedule_update', 'schedule_delete', 'schedule_execute', 'schedule_complete'])) {
            throw new \InvalidArgumentException("Invalid schedule action: {$action}");
        }

        // If schedule is provided, get the valve from it
        if ($schedule && !$valve) {
            $valve = IrrigationValve::where('node_uid', $schedule->node_uid)->first();
        }

        return IrrigationValveLog::create([
            'irrigation_valve_id' => $valve?->id,
            'device_id' => $valve?->device_id,
            'node_uid' => $schedule?->node_uid ?? $valve?->node_uid,
            'action' => $action,
            'trigger' => $trigger,
            'duration_seconds' => $schedule?->duration_minutes ? $schedule->duration_minutes * 60 : null,
            'notes' => $this->generateScheduleActionNote($action, $schedule),
            'metadata' => array_merge([
                'schedule_id' => $schedule?->id,
                'start_time' => $schedule?->start_time,
                'duration_minutes' => $schedule?->duration_minutes,
                'water_usage_target_liters' => $schedule?->water_usage_target_liters,
                'days_of_week' => $schedule?->days_of_week,
                'is_active' => $schedule?->is_active,
                'device_name' => $valve?->device?->device_name,
                'timestamp' => now(),
            ], $metadata),
            'user_id' => Auth::id(),
            'source_ip' => Request::ip(),
        ]);
    }

    /**
     * Bulk log multiple actions (useful for system operations)
     */
    public function logBulkActions(array $logEntries): void
    {
        $logs = [];
        foreach ($logEntries as $entry) {
            $logs[] = array_merge($entry, [
                'user_id' => Auth::id(),
                'source_ip' => Request::ip(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        IrrigationValveLog::insert($logs);
    }

    /**
     * Generate human-readable notes for device connection logs
     */
    private function generateDeviceConnectionNote(Device $device, string $action): string
    {
        $deviceName = $device->device_name ?? "Device #{$device->id}";
        $location = $device->location ? " at {$device->location}" : '';
        
        return match($action) {
            'device_connect' => "Device {$deviceName}{$location} came online",
            'device_disconnect' => "Device {$deviceName}{$location} went offline",
            default => "Device {$deviceName}{$location} status changed: {$action}",
        };
    }

    /**
     * Generate human-readable notes for valve control logs
     */
    private function generateValveControlNote(IrrigationValve $valve, string $action): string
    {
        $deviceName = $valve->device?->device_name ?? "Device #{$valve->device_id}";
        $valveDesc = $valve->description ?: 'Irrigation Valve';
        
        return match($action) {
            'open' => "Opened {$valveDesc} on {$deviceName}",
            'close' => "Closed {$valveDesc} on {$deviceName}",
            'toggle_mode' => "Toggled mode for {$valveDesc} on {$deviceName}",
            'system_auto_open' => "System automatically opened {$valveDesc} on {$deviceName}",
            'system_auto_close' => "System automatically closed {$valveDesc} on {$deviceName}",
            default => "Valve {$action} on {$deviceName}",
        };
    }

    /**
     * Generate human-readable notes for schedule action logs
     */
    private function generateScheduleActionNote(string $action, ?IrrigationValveSchedule $schedule): string
    {
        $scheduleInfo = $schedule ? "Schedule #{$schedule->id}" : 'Schedule';
        
        return match($action) {
            'schedule_create' => "Created new irrigation schedule",
            'schedule_update' => "Updated {$scheduleInfo}",
            'schedule_delete' => "Deleted {$scheduleInfo}",
            'schedule_execute' => "Started executing {$scheduleInfo}",
            'schedule_complete' => "Completed {$scheduleInfo}",
            default => "Schedule {$action}",
        };
    }

    /**
     * Get recent logs with filters
     */
    public function getRecentLogs(int $limit = 100, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = IrrigationValveLog::with(['valve', 'device'])
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['trigger'])) {
            $query->where('trigger', $filters['trigger']);
        }

        if (isset($filters['device_id'])) {
            $query->where('device_id', $filters['device_id']);
        }

        if (isset($filters['valve_id'])) {
            $query->where('irrigation_valve_id', $filters['valve_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->get();
    }
}
