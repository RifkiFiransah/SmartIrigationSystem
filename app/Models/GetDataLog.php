<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GetDataLog extends Model
{
    protected $table = 'getdata_logs';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'sesi_id_getdata',
        'waktu_mulai',
        'waktu_selesai',
        'node_sukses',
        'node_gagal',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    public function sensorNodeData()
    {
        return $this->hasMany(SensorNodeData::class, 'sesi_id_getdata', 'sesi_id_getdata');
    }

    public function sensorWeatherData()
    {
        return $this->hasMany(SensorWeatherData::class, 'sesi_id_getdata', 'sesi_id_getdata');
    }
}