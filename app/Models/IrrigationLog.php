<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IrrigationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'irrigation_control_id',
        'irrigation_schedule_id',
        'action',
        'trigger_type',
        'triggered_by',
        'started_at',
        'ended_at',
        'duration_seconds',
        'water_flow_rate',
        'total_water_used',
        'sensor_data_snapshot',
        'status',
        'notes',
        'error_message',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_seconds' => 'integer',
        'water_flow_rate' => 'decimal:2',
        'total_water_used' => 'decimal:2',
        'sensor_data_snapshot' => 'array',
    ];

    /**
     * Relasi ke IrrigationControl
     */
    public function irrigationControl(): BelongsTo
    {
        return $this->belongsTo(IrrigationControl::class);
    }

    /**
     * Relasi ke IrrigationSchedule
     */
    public function irrigationSchedule(): BelongsTo
    {
        return $this->belongsTo(IrrigationSchedule::class);
    }

    /**
     * Scope untuk log hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('started_at', today());
    }

    /**
     * Scope untuk log yang sedang berjalan
     */
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    /**
     * Scope untuk log berdasarkan trigger
     */
    public function scopeByTrigger($query, string $trigger)
    {
        return $query->where('trigger_type', $trigger);
    }

    /**
     * Scope untuk log berdasarkan action
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Calculate duration ketika selesai
     */
    public function calculateDuration(): void
    {
        if ($this->started_at && $this->ended_at) {
            $this->duration_seconds = $this->ended_at->diffInSeconds($this->started_at);
        }
    }

    /**
     * Mark log sebagai selesai
     */
    public function markAsCompleted(?string $notes = null): void
    {
        $this->ended_at = now();
        $this->status = 'completed';
        $this->calculateDuration();
        
        if ($notes) {
            $this->notes = $notes;
        }
        
        $this->save();
    }

    /**
     * Mark log sebagai gagal
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->ended_at = now();
        $this->status = 'failed';
        $this->error_message = $errorMessage;
        $this->calculateDuration();
        $this->save();
    }

    /**
     * Mark log sebagai dibatalkan
     */
    public function markAsCancelled(?string $reason = null): void
    {
        $this->ended_at = now();
        $this->status = 'cancelled';
        $this->calculateDuration();
        
        if ($reason) {
            $this->notes = $reason;
        }
        
        $this->save();
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_seconds) {
            return '-';
        }

        $hours = intval($this->duration_seconds / 3600);
        $minutes = intval(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Get action dengan emoji
     */
    public function getActionIconAttribute(): string
    {
        return match($this->action) {
            'start' => '▶️',
            'stop' => '⏹️',
            'pause' => '⏸️',
            'error' => '❌',
            'manual_override' => '👤',
            default => '➡️'
        };
    }

    /**
     * Get status dengan emoji
     */
    public function getStatusIconAttribute(): string
    {
        return match($this->status) {
            'running' => '🟢',
            'completed' => '✅',
            'failed' => '❌',
            'cancelled' => '🟠',
            default => '⚪'
        };
    }

    /**
     * Check apakah sedang berjalan
     */
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    /**
     * Calculate water usage dari flow rate dan durasi
     */
    public function calculateWaterUsage(): void
    {
        if ($this->water_flow_rate && $this->duration_seconds) {
            // Flow rate dalam L/menit, duration dalam detik
            $durationMinutes = $this->duration_seconds / 60;
            $this->total_water_used = $this->water_flow_rate * $durationMinutes;
        }
    }
}
