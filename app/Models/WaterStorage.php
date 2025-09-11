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
        'capacity_liters',
    'height_cm',
    'calibration_offset_cm',
    'last_height_cm',
        'current_volume_liters',
        'max_daily_usage',
        'status',
        'notes',
    ];

    protected $casts = [
        'capacity_liters' => 'decimal:2',
        'current_volume_liters' => 'decimal:2',
        'max_daily_usage' => 'decimal:2',
        'area_size_sqm' => 'decimal:2',
    'height_cm' => 'decimal:2',
    'calibration_offset_cm' => 'decimal:2',
    'last_height_cm' => 'decimal:2',
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

    // Relasi ke usage logs
    public function usageLogs()
    {
        return $this->hasMany(WaterUsageLog::class)->latest('usage_date');
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
        if ($this->capacity_liters <= 0) {
            return 0;
        }
        return round(($this->current_volume_liters / $this->capacity_liters) * 100, 2);
    }

    // Accessor untuk backward compatibility - total_capacity
    public function getTotalCapacityAttribute(): float
    {
        return $this->capacity_liters;
    }

    // Accessor untuk backward compatibility - current_volume
    public function getCurrentVolumeAttribute(): float
    {
        return $this->current_volume_liters;
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
        $old = $this->current_volume_liters;
        $this->current_volume_liters = min($newVolume, $this->capacity_liters);
        $this->status = $this->auto_status;
        $this->save();

        // Catat penggunaan jika volume turun
        $delta = $old - $this->current_volume_liters;
        if ($delta > 0.01) {
            $this->usageLogs()->create([
                'usage_date' => now()->toDateString(),
                'volume_used_l' => $delta,
                'source' => 'adjust',
                'meta' => [
                    'method' => 'updateVolume',
                ],
            ]);
        }
    }

    // Hitung volume dari ketinggian (digunakan endpoint sederhana)
    public function updateFromHeight(?float $measuredHeightCm, ?float $providedCapacity = null): void
    {
        if (!$measuredHeightCm || $measuredHeightCm < 0 || !$this->height_cm || $this->height_cm <= 0) {
            return; // tidak cukup data
        }

        $effectiveHeight = max($measuredHeightCm - ($this->calibration_offset_cm ?? 0), 0);
        $ratio = min($effectiveHeight / $this->height_cm, 1);
        $capacity = $providedCapacity ?: $this->capacity_liters;

        $newVolume = round($ratio * $capacity, 2);
        $old = $this->current_volume_liters;

        $this->last_height_cm = $measuredHeightCm;
        $this->last_height_recorded_at = now();
        $this->current_volume_liters = min($newVolume, $capacity);
        $this->status = $this->auto_status;
        $this->save();

        $delta = $old - $this->current_volume_liters;
        if ($delta > 0.01) {
            $this->usageLogs()->create([
                'usage_date' => now()->toDateString(),
                'volume_used_l' => $delta,
                'source' => 'auto_calc',
                'meta' => [
                    'method' => 'updateFromHeight',
                    'measured_height_cm' => $measuredHeightCm,
                    'ratio' => $ratio,
                ],
            ]);
        }
    }

    // Method untuk prediksi kehabisan air
    public function getDaysUntilEmptyAttribute(): ?int
    {
        if (!$this->max_daily_usage || $this->max_daily_usage <= 0) {
            return null;
        }
        
        return (int) ceil($this->current_volume_liters / $this->max_daily_usage);
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
            'tank_capacity' => $this->capacity_liters,
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

    // Total penggunaan hari ini
    public function getTodayUsageAttribute(): float
    {
        return (float) ($this->usageLogs()->whereDate('usage_date', today())->sum('volume_used_l') ?: 0);
    }

    // Ambil agregasi penggunaan harian terakhir n hari (default 30)
    public function getDailyUsage(int $days = 30)
    {
        $from = now()->subDays($days - 1)->toDateString();
        return $this->usageLogs()
            ->selectRaw('usage_date, SUM(volume_used_l) as total_l')
            ->where('usage_date', '>=', $from)
            ->groupBy('usage_date')
            ->orderBy('usage_date')
            ->get()
            ->map(fn($r)=>[
                'date' => $r->usage_date,
                'total_l' => (float) $r->total_l,
            ]);
    }
}
