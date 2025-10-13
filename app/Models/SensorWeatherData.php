<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorWeatherData extends Model
{
    protected $table = 'sensor_weather_data';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'sesi_id_getdata',
        'node_id',
        'voltage',
        'current',
        'power',
        'light',
        'rain',
        'rain_adc',
        'wind',
        'wind_pulse',
        'humidity',
        'temp_dht',
        'rssi',
        'snr',
        'signal_quality',
    ];

    protected $casts = [
        'voltage' => 'decimal:2',
        'current' => 'decimal:2',
        'power' => 'decimal:2',
        'light' => 'decimal:2',
        'rain' => 'decimal:2',
        'wind' => 'decimal:2',
        'humidity' => 'decimal:2',
        'temp_dht' => 'decimal:2',
        'rssi' => 'decimal:2',
        'snr' => 'decimal:2',
    ];

    public function node()
    {
        return $this->belongsTo(Node::class, 'node_id', 'node_id');
    }

    public function getDataLog()
    {
        return $this->belongsTo(GetDataLog::class, 'sesi_id_getdata', 'sesi_id_getdata');
    }
}