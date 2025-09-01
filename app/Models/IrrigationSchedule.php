<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class IrrigationSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_name',
        'irrigation_control_id',
        'schedule_type',
        'start_time',
        'duration_minutes',
        'days_of_week',
        'trigger_conditions',
        'is_active',
        'is_enabled',
        'last_run_at',
        'next_run_at',
        'run_count',
        'description',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i:s',
        'days_of_week' => 'array',
        'trigger_conditions' => 'array',
        'is_active' => 'boolean',
        'is_enabled' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'run_count' => 'integer',
        'duration_minutes' => 'integer',
    ];

    /**
     * Relasi ke IrrigationControl
     */
    public function irrigationControl(): BelongsTo
    {
        return $this->belongsTo(IrrigationControl::class);
    }

    /**
     * Relasi ke IrrigationLog
     */
    public function logs(): HasMany
    {
        return $this->hasMany(IrrigationLog::class);
    }

    /**
     * Scope untuk schedule aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_enabled', true);
    }

    /**
     * Scope untuk schedule yang harus dijalankan
     */
    public function scopeDue($query)
    {
        return $query->active()
            ->where('next_run_at', '<=', now());
    }

    /**
     * Scope untuk schedule berdasarkan tipe
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('schedule_type', $type);
    }

    /**
     * Check apakah schedule harus dijalankan sekarang
     */
    public function isDue(): bool
    {
        if (!$this->is_active || !$this->is_enabled) {
            return false;
        }

        return $this->next_run_at && $this->next_run_at <= now();
    }

    /**
     * Hitung next run time berdasarkan schedule type
     */
    public function calculateNextRun(): ?Carbon
    {
        $now = now();
        
        switch ($this->schedule_type) {
            case 'daily':
                return $this->calculateDailyNextRun($now);
                
            case 'weekly':
                return $this->calculateWeeklyNextRun($now);
                
            case 'sensor_based':
                // Untuk sensor based, next run ditentukan oleh kondisi sensor
                return null;
                
            case 'custom':
                // Implementasi custom logic
                return null;
                
            default:
                return null;
        }
    }

    /**
     * Calculate next run untuk daily schedule
     */
    protected function calculateDailyNextRun(Carbon $now): Carbon
    {
        $startTime = Carbon::createFromFormat('H:i:s', $this->start_time->format('H:i:s'));
        $nextRun = $now->copy()->setTime($startTime->hour, $startTime->minute, $startTime->second);
        
        // Jika waktu sudah lewat hari ini, set ke besok
        if ($nextRun <= $now) {
            $nextRun->addDay();
        }
        
        return $nextRun;
    }

    /**
     * Calculate next run untuk weekly schedule
     */
    protected function calculateWeeklyNextRun(Carbon $now): Carbon
    {
        if (empty($this->days_of_week)) {
            return $this->calculateDailyNextRun($now);
        }

        $startTime = Carbon::createFromFormat('H:i:s', $this->start_time->format('H:i:s'));
        $currentDayOfWeek = $now->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.
        
        // Cari hari terdekat yang cocok
        $nextRunDay = null;
        $daysToAdd = 0;
        
        for ($i = 0; $i <= 7; $i++) {
            $checkDay = ($currentDayOfWeek + $i) % 7;
            if (in_array($checkDay, $this->days_of_week)) {
                $nextRunDay = $checkDay;
                $daysToAdd = $i;
                
                // Jika hari ini tapi waktu sudah lewat, cari hari berikutnya
                if ($i === 0) {
                    $todaySchedule = $now->copy()->setTime($startTime->hour, $startTime->minute, $startTime->second);
                    if ($todaySchedule <= $now) {
                        continue;
                    }
                }
                break;
            }
        }
        
        $nextRun = $now->copy()->addDays($daysToAdd)->setTime($startTime->hour, $startTime->minute, $startTime->second);
        return $nextRun;
    }

    /**
     * Update next run time
     */
    public function updateNextRun(): void
    {
        $this->next_run_at = $this->calculateNextRun();
        $this->save();
    }

    /**
     * Mark schedule sebagai dijalankan
     */
    public function markAsRun(): void
    {
        $this->last_run_at = now();
        $this->run_count++;
        $this->updateNextRun();
        $this->save();
    }

    /**
     * Get deskripsi schedule
     */
    public function getDescriptionAttribute(): string
    {
        $desc = $this->attributes['description'] ?? '';
        
        if (empty($desc)) {
            $desc = match($this->schedule_type) {
                'daily' => "Daily at {$this->start_time->format('H:i')} for {$this->duration_minutes} minutes",
                'weekly' => "Weekly on " . $this->getWeekDaysText() . " at {$this->start_time->format('H:i')} for {$this->duration_minutes} minutes",
                'sensor_based' => "Triggered by sensor conditions",
                default => "Custom schedule"
            };
        }
        
        return $desc;
    }

    /**
     * Get text untuk hari dalam seminggu
     */
    protected function getWeekDaysText(): string
    {
        if (empty($this->days_of_week)) {
            return 'every day';
        }

        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $selectedDays = array_map(fn($day) => $dayNames[$day], $this->days_of_week);
        
        return implode(', ', $selectedDays);
    }
}
