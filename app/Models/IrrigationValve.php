<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class IrrigationValve extends Model
{
    use HasFactory;

    protected $fillable = [
        'node_uid',
        'device_id',
        'gpio_pin',
        'status', // open|closed
        'mode',   // auto|manual
        'is_active',
        'last_open_at',
        'last_close_at',
        'last_evaluated_at',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_open_at' => 'datetime',
        'last_close_at' => 'datetime',
        'last_evaluated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $valve) {
            // Always generate a unique node_uid, even if one is provided
            do {
                $candidate = 'NODE-' . Str::upper(Str::random(6));
            } while (self::where('node_uid', $candidate)->exists());
            $valve->node_uid = $candidate;
        });
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function open(?int $maxDurationMinutes = null, string $trigger = 'manual'): void
    {
        $this->status = 'open';
        $this->last_open_at = now();
        $this->save();
        $this->recordAdvancedLog('open', $trigger, $maxDurationMinutes ? $maxDurationMinutes * 60 : null);
    }

    public function close(string $trigger = 'manual'): void
    {
        $this->status = 'closed';
        $this->last_close_at = now();
        $this->save();
        $this->recordAdvancedLog('close', $trigger);
    }

    public function toggleMode(string $trigger = 'manual'): void
    {
        $this->mode = $this->mode === 'auto' ? 'manual' : 'auto';
        $this->save();
        $this->recordAdvancedLog('toggle_mode', $trigger);
    }

    /**
     * Open valve automatically by system
     */
    public function systemOpen(?int $durationSeconds = null): void
    {
        $this->status = 'open';
        $this->last_open_at = now();
        $this->save();
        $this->recordAdvancedLog('system_auto_open', 'system', $durationSeconds);
    }

    /**
     * Close valve automatically by system
     */
    public function systemClose(): void
    {
        $this->status = 'closed';
        $this->last_close_at = now();
        $this->save();
        $this->recordAdvancedLog('system_auto_close', 'system');
    }

    /**
     * Record valve action using the advanced logging service
     */
    protected function recordAdvancedLog(string $action, string $trigger, ?int $durationSeconds = null): void
    {
        try {
            $logService = app(\App\Services\IrrigationValveLogService::class);
            $logService->logValveControl($this, $action, $trigger, $durationSeconds);
        } catch (\Throwable $e) {
            // Fallback to simple logging
            $this->recordLog($action, $trigger, $durationSeconds ? intval($durationSeconds / 60) : null);
        }
    }

    /**
     * Legacy logging method (kept for backward compatibility)
     */
    protected function recordLog(string $action, string $trigger, ?int $durationMinutes = null): void
    {
        // Log only if model exists and table available; swallow errors silently
        try {
            if (class_exists(\App\Models\IrrigationValveLog::class)) {
                \App\Models\IrrigationValveLog::create([
                    'irrigation_valve_id' => $this->id,
                    'node_uid' => $this->node_uid,
                    'action' => $action,
                    'trigger' => $trigger,
                    'duration_seconds' => $durationMinutes ? $durationMinutes * 60 : null,
                    'notes' => null,
                ]);
            }
        } catch (\Throwable $e) {
            // no-op
        }
    }
}
