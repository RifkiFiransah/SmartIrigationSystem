<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use HasFactory;

    protected $guarded = ['device_id'];

    public static function boot(){
        parent::boot();

        static::creating(function ($device) {
            if(empty($device->device_id)){
                $device->device_id = 'DEVICE_' . str_pad((string)(self::max('id') + 1), 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function SensorData() : HasMany
    {
        return $this->hasMany(SensorData::class);
    }

    public function waterStorages() : HasMany
    {
        return $this->hasMany(WaterStorage::class);
    }

    public function waterUsageLogs() : HasMany
    {
        return $this->hasMany(WaterUsageLog::class);
    }
}
