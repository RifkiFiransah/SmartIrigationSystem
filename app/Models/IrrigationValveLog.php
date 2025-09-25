<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IrrigationValveLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'irrigation_valve_id',
        'device_id',
        'node_uid',
        'action',      // open|close|toggle_mode|device_connect|device_disconnect|schedule_create|etc
        'trigger',     // manual|auto|schedule|api|system|device_event|admin_panel|mobile_app|web_interface
        'duration_seconds',
        'notes',
        'metadata',    // JSON field for additional context
        'user_id',     // User who triggered the action
        'source_ip',   // IP address of the request
    ];

    protected $casts = [
        'metadata' => 'array',
        'duration_seconds' => 'integer',
    ];

    public function valve(): BelongsTo
    {
        return $this->belongsTo(IrrigationValve::class, 'irrigation_valve_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    // Helper methods to identify log types
    public function isDeviceConnectionLog(): bool
    {
        return in_array($this->action, ['device_connect', 'device_disconnect']);
    }

    public function isValveControlLog(): bool
    {
        return in_array($this->action, ['open', 'close', 'toggle_mode', 'system_auto_open', 'system_auto_close']);
    }

    public function isScheduleLog(): bool
    {
        return in_array($this->action, ['schedule_create', 'schedule_update', 'schedule_delete', 'schedule_execute', 'schedule_complete']);
    }
}
