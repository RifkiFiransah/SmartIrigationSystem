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
        'zone_name',
        'zone_description',
        'area_name',
        'irrigation_lines',
        'total_lines',
        'area_size_sqm',
        'plant_types',
        'irrigation_system_type',
        'device_id',
        'associated_devices',
        'total_capacity',
        'current_volume',
        'max_daily_usage',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_capacity' => 'decimal:2',
        'current_volume' => 'decimal:2',
        'max_daily_usage' => 'decimal:2',
        'area_size_sqm' => 'decimal:2',
        'total_lines' => 'integer',
        'percentage' => 'decimal:2',
        'associated_devices' => 'array',
        'irrigation_lines' => 'array',
    ];

    // Relasi ke Device
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    // Method untuk mendapatkan semua device di zona ini
    public function getAllZoneDevices()
    {
        $devices = collect();
        
        // Tambahkan primary device
        if ($this->device) {
            $devices->push([
                'device' => $this->device,
                'role' => 'Primary Node'
            ]);
        }
        
        // Tambahkan associated devices
        if ($this->associated_devices) {
            foreach ($this->associated_devices as $assocDevice) {
                $device = Device::find($assocDevice['device_id']);
                if ($device) {
                    $devices->push([
                        'device' => $device,
                        'role' => $assocDevice['role'] ?? 'Additional Node'
                    ]);
                }
            }
        }
        
        return $devices;
    }

    // Method untuk mendapatkan total node di zona
    public function getTotalNodesAttribute(): int
    {
        $count = $this->device ? 1 : 0;
        $count += is_array($this->associated_devices) ? count($this->associated_devices) : 0;
        return $count;
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

    // Method untuk prediksi kehabisan air
    public function getDaysUntilEmptyAttribute(): ?int
    {
        if (!$this->max_daily_usage || $this->max_daily_usage <= 0) {
            return null;
        }
        
        return (int) ceil($this->current_volume / $this->max_daily_usage);
    }

    // Method untuk cek apakah perlu refill
    public function needsRefill(): bool
    {
        $daysUntilEmpty = $this->days_until_empty;
        return $this->percentage <= 25 || ($daysUntilEmpty && $daysUntilEmpty <= 3);
    }

    // Method untuk mendapatkan zona info lengkap
    public function getZoneInfoAttribute(): array
    {
        return [
            'zone_name' => $this->zone_name,
            'zone_description' => $this->zone_description,
            'area_name' => $this->area_name,
            'total_nodes' => $this->total_nodes,
            'primary_device' => $this->device?->device_name,
            'tank_capacity' => $this->total_capacity,
            'current_level' => $this->percentage,
            'estimated_days_left' => $this->days_until_empty,
            'needs_refill' => $this->needsRefill(),
            'irrigation_info' => $this->irrigation_info,
        ];
    }

    // Method untuk mendapatkan info irigasi lengkap
    public function getIrrigationInfoAttribute(): array
    {
        return [
            'total_lines' => $this->total_lines,
            'area_size_sqm' => $this->area_size_sqm,
            'plant_types' => $this->plant_types,
            'irrigation_system_type' => $this->irrigation_system_type,
            'lines_detail' => $this->irrigation_lines ?? [],
            'water_per_sqm_per_day' => $this->area_size_sqm > 0 && $this->max_daily_usage > 0 ? 
                round($this->max_daily_usage / $this->area_size_sqm, 2) : 0,
        ];
    }

    // Method untuk mendapatkan jalur irigasi aktif
    public function getActiveIrrigationLines()
    {
        if (!$this->irrigation_lines) {
            return collect();
        }

        return collect($this->irrigation_lines)->map(function ($line) {
            return (object) [
                'line_id' => $line['line_id'] ?? 'unknown',
                'line_name' => $line['line_name'] ?? 'Unnamed Line',
                'line_type' => $line['line_type'] ?? 'drip',
                'plant_count' => $line['plant_count'] ?? 0,
                'coverage_sqm' => $line['coverage_sqm'] ?? 0,
                'flow_rate_lpm' => $line['flow_rate_lpm'] ?? 0,
                'status' => $line['status'] ?? 'active',
                'nodes' => $line['nodes'] ?? [],
            ];
        });
    }

    // Method untuk menghitung total tanaman di area
    public function getTotalPlantsAttribute(): int
    {
        if (!$this->irrigation_lines) {
            return 0;
        }

        return collect($this->irrigation_lines)->sum('plant_count');
    }

    // Method untuk menghitung efisiensi air
    public function getWaterEfficiencyAttribute(): float
    {
        if ($this->area_size_sqm <= 0 || $this->max_daily_usage <= 0) {
            return 0;
        }

        $litersPerSqmPerDay = $this->max_daily_usage / $this->area_size_sqm;
        
        // Efisiensi berdasarkan standar irigasi (3-5 L/m²/hari = efisien)
        if ($litersPerSqmPerDay <= 3) return 100;
        if ($litersPerSqmPerDay <= 5) return 80;
        if ($litersPerSqmPerDay <= 8) return 60;
        return 40;
    }
}
