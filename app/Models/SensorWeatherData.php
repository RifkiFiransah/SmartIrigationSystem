<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorWeatherData extends Model
{
    protected $table = 'sensor_weather_data';

    protected $guarded = ['id'];

    public function getDataLog()
    {
        return $this->belongsTo(GetDataLog::class, 'sesi_id_getdata', 'sesi_id_getdata');
    }
}
