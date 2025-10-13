<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorNodeData extends Model
{
    protected $table = 'sensor_node_data';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'sesi_id_getdata',
        'node_id',
        'rssi_dbm',
        'snr_db',
        'voltage_v',
        'current_ma',
        'power_mw',
        'temp_c',
        'soil_pct',
        'soil_adc',
        'ts_counter',
        'received_at',
    ];

    protected $casts = [
        'rssi_dbm' => 'decimal:2',
        'snr_db' => 'decimal:2',
        'voltage_v' => 'decimal:2',
        'current_ma' => 'decimal:2',
        'power_mw' => 'decimal:2',
        'temp_c' => 'decimal:2',
        'soil_pct' => 'decimal:2',
        'received_at' => 'datetime',
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