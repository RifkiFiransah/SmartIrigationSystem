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
        'days_of_week',         // [0..6], 0=Sun
        'is_active',
        'last_run_at',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
    ];
}
