<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GetDataLog extends Model
{
    protected $table = 'getdata_logs';

    protected $guarded = ['id']; 

    public function sensorWeatherData()
    {
        return $this->hasMany(SensorWeatherData::class, 'sesi_id_getdata', 'sesi_id_getdata');
    }
}
