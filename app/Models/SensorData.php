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
    ];

    public function device() : BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
