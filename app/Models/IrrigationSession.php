<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IrrigationSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'irrigation_daily_plan_id',
        'session_index',
        'scheduled_time',
        'planned_volume_l',
        'adjusted_volume_l',
        'actual_volume_l',
        'status',
        'started_at',
        'completed_at',
        'meta',
    ];

    protected $casts = [
        'planned_volume_l' => 'decimal:2',
        'adjusted_volume_l' => 'decimal:2',
        'actual_volume_l' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'meta' => 'array',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(IrrigationDailyPlan::class, 'irrigation_daily_plan_id');
    }
}
