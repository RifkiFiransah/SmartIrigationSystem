<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IrrigationValveSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'node_uid',
        'start_time',           // HH:MM:SS
        'duration_minutes',
        'water_usage_target_liters',  // Target penggunaan air dalam liter
        'days_of_week',         // [0..6], 0=Sun
        'is_active',
        'last_run_at',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'water_usage_target_liters' => 'decimal:2',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
    ];
}
