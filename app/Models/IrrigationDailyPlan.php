<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IrrigationDailyPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_date',
        'base_total_volume_l',
        'adjusted_total_volume_l',
        'adjustment_factors',
        'status',
    ];

    protected $casts = [
        'plan_date' => 'date',
        'base_total_volume_l' => 'decimal:2',
        'adjusted_total_volume_l' => 'decimal:2',
        'adjustment_factors' => 'array',
    ];

    public function sessions(): HasMany
    {
        return $this->hasMany(IrrigationSession::class)->orderBy('session_index');
    }

    public function getCompletedVolumeAttribute(): float
    {
        return (float) $this->sessions()->whereNotNull('actual_volume_l')->sum('actual_volume_l');
    }
}
