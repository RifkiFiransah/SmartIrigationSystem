<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    protected $table = 'node';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'node_id',
        'group',
        'kode_perlakuan',
        'lokasi',
        'keterangan',
        'waktu_dibuat',
        'waktu_update',
    ];

    protected $casts = [
        'waktu_dibuat' => 'datetime',
        'waktu_update' => 'datetime',
    ];

    public function sensorNodeData()
    {
        return $this->hasMany(SensorNodeData::class, 'node_id', 'node_id');
    }

    public function sensorWeatherData()
    {
        return $this->hasMany(SensorWeatherData::class, 'node_id', 'node_id');
    }
}