<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorData extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'recorded_at' => 'datetime',
        'temperature' => 'float',
        'humidity' => 'float',
        'soil_moisture' => 'float',
        'water_flow' => 'float',
    'light_intensity' => 'float',
    // New fields
    'temperature_c' => 'float',
    'soil_moisture_pct' => 'integer',
    'water_height_cm' => 'integer',
    'water_volume_l' => 'decimal:2',
    'light_lux' => 'integer',
    'wind_speed_ms' => 'decimal:2',
    'ina226_bus_voltage_v' => 'decimal:3',
    'ina226_shunt_voltage_mv' => 'integer',
    'ina226_current_ma' => 'decimal:3',
    'ina226_power_mw' => 'decimal:3',
    'device_ts' => 'datetime',
    'device_ts_unix' => 'integer',
    'flags' => 'array',
    ];

    public function device() : BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
