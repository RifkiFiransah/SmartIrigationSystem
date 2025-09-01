<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IrrigationControl extends Model
{
    use HasFactory;

    protected $fillable = [
        'control_name',
        'control_type',
        'device_id',
        'pin_number',
        'status',
        'mode',
        'duration_minutes',
        'last_activated_at',
        'last_deactivated_at',
        'settings',
        'is_active',
        'description',
    ];

    protected $casts = [
        'settings' => 'array',
        'last_activated_at' => 'datetime',
        'last_deactivated_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke Device
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Relasi ke IrrigationSchedule
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(IrrigationSchedule::class);
    }

    /**
     * Relasi ke IrrigationLog
     */
    public function logs(): HasMany
    {
        return $this->hasMany(IrrigationLog::class);
    }

    /**
     * Scope untuk status tertentu
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk control yang sedang aktif
     */
    public function scopeRunning($query)
    {
        return $query->where('status', 'on');
    }

    /**
     * Scope untuk mode otomatis
     */
    public function scopeAutoMode($query)
    {
        return $query->where('mode', 'auto');
    }

    /**
     * Check apakah sedang berjalan
     */
    public function isRunning(): bool
    {
        return $this->status === 'on';
    }

    /**
     * Check apakah dalam mode otomatis
     */
    public function isAutoMode(): bool
    {
        return $this->mode === 'auto';
    }

    /**
     * Get durasi total hari ini
     */
    public function getTodayDurationAttribute(): int
    {
        return $this->logs()
            ->whereDate('started_at', today())
            ->where('status', 'completed')
            ->sum('duration_seconds');
    }

    /**
     * Get status dengan emoji
     */
    public function getStatusIconAttribute(): string
    {
        return match($this->status) {
            'on' => '🟢',
            'off' => '🔴',
            'auto' => '🤖',
            'manual' => '👤',
            'error' => '❌',
            default => '⚪'
        };
    }
}
