<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvironmentReading extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'recorded_at' => 'datetime',
        'light_lux' => 'integer',
        'wind_speed_ms' => 'decimal:2',
        'external_temp_c' => 'decimal:2',
        'external_humidity_pct' => 'integer',
        'rainfall_mm' => 'decimal:2',
        'meta' => 'array',
    ];
}
