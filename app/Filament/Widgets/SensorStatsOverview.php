<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use App\Models\Device;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class SensorStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;
    
    // Auto refresh setiap 30 detik
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // Ambil data terkini dari semua device aktif
        $latestData = SensorData::with('device')
            ->whereHas('device', function ($query) {
                $query->where('is_active', true);
            })
            ->latest('recorded_at')
            ->first();

        // Statistik untuk perbandingan dengan 24 jam lalu
        $avgLast24h = SensorData::where('recorded_at', '>=', now()->subHours(24))
            ->selectRaw('
                AVG(temperature_c) as avg_temp,
                AVG(soil_moisture_pct) as avg_soil,
                AVG(water_volume_l) as avg_volume,
                AVG(light_lux) as avg_light,
                AVG(wind_speed_ms) as avg_wind,
                AVG(water_height_cm) as avg_height,
                AVG(ina226_power_mw) as avg_power
            ')
            ->first();

        // Hitung perubahan dari rata-rata 24 jam
        $tempChange = $this->calculateChange($latestData?->temperature_c, $avgLast24h?->avg_temp);
        $soilChange = $this->calculateChange($latestData?->soil_moisture_pct, $avgLast24h?->avg_soil);
        $volumeChange = $this->calculateChange($latestData?->water_volume_l, $avgLast24h?->avg_volume);
        $lightChange = $this->calculateChange($latestData?->light_lux, $avgLast24h?->avg_light);
        $windChange = $this->calculateChange($latestData?->wind_speed_ms, $avgLast24h?->avg_wind);
        $heightChange = $this->calculateChange($latestData?->water_height_cm, $avgLast24h?->avg_height);
        $powerChange = $this->calculateChange($latestData?->ina226_power_mw, $avgLast24h?->avg_power);

        return [
            Stat::make('Suhu', $this->formatValue($latestData?->temperature_c, '°C'))
                ->description($this->getSensorDescription('Suhu', $tempChange, $latestData?->temperature_c, [15, 35]))
                ->descriptionIcon($this->getIcon($latestData?->temperature_c, [15, 35], 'heroicon-m-fire', 'heroicon-m-sun', 'heroicon-m-cube-transparent'))
                ->color($this->getColor($latestData?->temperature_c, [15, 35]))
                ->chart($this->getMiniChart('temperature_c')),

            Stat::make('Kelembapan Tanah', $this->formatValue($latestData?->soil_moisture_pct, '%'))
                ->description($this->getSensorDescription('Tanah', $soilChange, $latestData?->soil_moisture_pct, [30, 70]))
                ->descriptionIcon($this->getIcon($latestData?->soil_moisture_pct, [30, 70], 'heroicon-m-beaker', 'heroicon-m-globe-alt', 'heroicon-m-exclamation-triangle'))
                ->color($this->getColor($latestData?->soil_moisture_pct, [30, 70]))
                ->chart($this->getMiniChart('soil_moisture_pct')),

            Stat::make('Volume Air', $this->formatValue($latestData?->water_volume_l, ' L'))
                ->description($this->getSensorDescription('Volume', $volumeChange, $latestData?->water_volume_l, [50, 200]))
                ->descriptionIcon($this->getIcon($latestData?->water_volume_l, [50, 200], 'heroicon-m-arrow-up', 'heroicon-m-arrow-right', 'heroicon-m-arrow-down'))
                ->color($this->getColor($latestData?->water_volume_l, [50, 200]))
                ->chart($this->getMiniChart('water_volume_l')),

            Stat::make('Cahaya', $this->formatValue($latestData?->light_lux, ' Lux'))
                ->description($this->getSensorDescription('Cahaya', $lightChange, $latestData?->light_lux, [1000, 50000]))
                ->descriptionIcon($this->getIcon($latestData?->light_lux, [1000, 50000], 'heroicon-m-sun', 'heroicon-m-light-bulb', 'heroicon-m-moon'))
                ->color($this->getColor($latestData?->light_lux, [1000, 50000]))
                ->chart($this->getMiniChart('light_lux')),

            Stat::make('Angin', $this->formatValue($latestData?->wind_speed_ms, ' m/s'))
                ->description($this->getSensorDescription('Angin', $windChange, $latestData?->wind_speed_ms, [2, 10]))
                ->descriptionIcon($this->getIcon($latestData?->wind_speed_ms, [2, 10], 'heroicon-m-arrow-trending-up', 'heroicon-m-minus', 'heroicon-m-pause'))
                ->color($this->getColor($latestData?->wind_speed_ms, [2, 10]))
                ->chart($this->getMiniChart('wind_speed_ms')),

            Stat::make('Tinggi Air', $this->formatValue($latestData?->water_height_cm, ' cm'))
                ->description($this->getSensorDescription('Tinggi', $heightChange, $latestData?->water_height_cm, [20, 80]))
                ->descriptionIcon($this->getIcon($latestData?->water_height_cm, [20, 80], 'heroicon-m-arrow-up', 'heroicon-m-minus', 'heroicon-m-arrow-down'))
                ->color($this->getColor($latestData?->water_height_cm, [20, 80]))
                ->chart($this->getMiniChart('water_height_cm')),

            Stat::make('Daya INA226', $this->formatValue($latestData?->ina226_power_mw, ' mW'))
                ->description($this->getSensorDescription('Daya', $powerChange, $latestData?->ina226_power_mw, [100, 1000]))
                ->descriptionIcon($this->getIcon($latestData?->ina226_power_mw, [100, 1000], 'heroicon-m-bolt', 'heroicon-m-battery-50', 'heroicon-m-battery-0'))
                ->color($this->getColor($latestData?->ina226_power_mw, [100, 1000]))
                ->chart($this->getMiniChart('ina226_power_mw')),

            Stat::make('Status Sistem', $this->getSystemStatus())
                ->description($this->getSystemDescription())
                ->descriptionIcon($this->getSystemIcon())
                ->color($this->getSystemColor()),
        ];
    }

    private function calculateChange($current, $average): float
    {
        if (!$current || !$average || $average == 0) return 0;
        return (($current - $average) / $average) * 100;
    }

    private function formatValue($value, $unit): string
    {
        return $value !== null ? number_format($value, 2) . $unit : 'N/A';
    }

    private function getSensorDescription(string $type, float $change, $value, array $range): string
    {
        if ($value === null) return 'Tidak ada data';
        
        $status = $this->getStatus($value, $range);
        $changeText = '';
        if (abs($change) > 0.1) {
            $changeText = sprintf(' (%s%.1f%%)', $change > 0 ? '+' : '', $change);
        }
        
        return $status . $changeText;
    }

    private function getStatus($value, array $range): string
    {
        [$min, $max] = $range;
        return match (true) {
            $value < $min * 0.7 => 'Sangat Rendah',
            $value < $min => 'Rendah',
            $value <= $max => 'Normal',
            $value <= $max * 1.3 => 'Tinggi',
            default => 'Sangat Tinggi'
        };
    }

    private function getIcon($value, array $range, string $high, string $normal, string $low): string
    {
        if ($value === null) return 'heroicon-m-question-mark-circle';
        [$min, $max] = $range;
        return match (true) {
            $value > $max => $high,
            $value < $min => $low,
            default => $normal
        };
    }

    private function getColor($value, array $range): string
    {
        if ($value === null) return 'gray';
        [$min, $max] = $range;
        return match (true) {
            $value < $min * 0.5 || $value > $max * 1.5 => 'danger',
            $value < $min || $value > $max => 'warning',
            default => 'success'
        };
    }

    // System Status methods
    private function getSystemStatus(): string
    {
        $activeDevices = Device::where('is_active', true)->count();
        $criticalAlerts = SensorData::where('status', 'kritis')
            ->where('recorded_at', '>=', now()->subHours(24))
            ->count();
        
        if ($criticalAlerts > 0) {
            return 'KRITIS';
        }
        
        $warningAlerts = SensorData::where('status', 'peringatan')
            ->where('recorded_at', '>=', now()->subHours(24))
            ->count();
        
        if ($warningAlerts > 5) {
            return 'PERINGATAN';
        }
        
        return $activeDevices > 0 ? 'NORMAL' : 'OFFLINE';
    }

    private function getSystemDescription(): string
    {
        $activeDevices = Device::where('is_active', true)->count();
        $totalData = SensorData::whereDate('created_at', today())->count();
        
        return "{$activeDevices} device aktif • {$totalData} data hari ini";
    }

    private function getSystemIcon(): string
    {
        $status = $this->getSystemStatus();
        
        return match ($status) {
            'KRITIS' => 'heroicon-m-exclamation-triangle',
            'PERINGATAN' => 'heroicon-m-exclamation-circle',
            'OFFLINE' => 'heroicon-m-no-symbol',
            default => 'heroicon-m-check-circle'
        };
    }

    private function getSystemColor(): string
    {
        $status = $this->getSystemStatus();
        
        return match ($status) {
            'KRITIS' => 'danger',
            'PERINGATAN' => 'warning',
            'OFFLINE' => 'gray',
            default => 'success'
        };
    }

    // Chart methods untuk mini charts
    private function getMiniChart(string $field): array
    {
        $data = SensorData::where('recorded_at', '>=', now()->subHours(6))
            ->orderBy('recorded_at')
            ->pluck($field)
            ->take(12)
            ->toArray();

        return array_pad($data, 12, 0);
    }
}