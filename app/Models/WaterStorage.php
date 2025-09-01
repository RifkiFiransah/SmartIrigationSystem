<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaterStorage extends Model
{
    use HasFactory;

    protected $fillable = [
        'tank_name',
        'device_id',
        'total_capacity',
        'current_volume',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_capacity' => 'decimal:2',
        'current_volume' => 'decimal:2',
        'percentage' => 'decimal:2',
    ];

    // Relasi ke Device
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    // Accessor untuk mendapatkan persentase
    public function getPercentageAttribute(): float
    {
        if ($this->total_capacity <= 0) {
            return 0;
        }
        return round(($this->current_volume / $this->total_capacity) * 100, 2);
    }

    // Accessor untuk status otomatis berdasarkan persentase
    public function getAutoStatusAttribute(): string
    {
        $percentage = $this->percentage;
        
        if ($percentage >= 90) {
            return 'full';
        } elseif ($percentage <= 10) {
            return 'empty';
        } elseif ($percentage <= 25) {
            return 'low';
        } else {
            return 'normal';
        }
    }

    // Method untuk update volume
    public function updateVolume(float $newVolume): void
    {
        $this->current_volume = min($newVolume, $this->total_capacity);
        $this->status = $this->auto_status;
        $this->save();
    }
}
