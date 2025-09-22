<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use App\Models\Device;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Services\BMKGWeatherService;
use Illuminate\Support\Facades\App;

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

        // Statistik untuk perbandingan dengan 24 jam lalu (hanya metric device inti)
        $avgLast24h = SensorData::where('recorded_at', '>=', now()->subHours(24))
            ->selectRaw('
                AVG(ground_temperature_c) as avg_temp,
                AVG(soil_moisture_pct) as avg_soil,
                AVG(irrigation_usage_total_l) as avg_irrigation,
                AVG(battery_voltage_v) as avg_battery,
                AVG(ina226_power_mw) as avg_power
            ')
            ->first();

        // Hitung perubahan dari rata-rata 24 jam
        $tempChange = $this->calculateChange($latestData?->ground_temperature_c, $avgLast24h?->avg_temp);
        $soilChange = $this->calculateChange($latestData?->soil_moisture_pct, $avgLast24h?->avg_soil);
        $irrigationChange = $this->calculateChange($latestData?->irrigation_usage_total_l, $avgLast24h?->avg_irrigation);
        $batteryChange = $this->calculateChange($latestData?->battery_voltage_v, $avgLast24h?->avg_battery);
        $powerChange = $this->calculateChange($latestData?->ina226_power_mw, $avgLast24h?->avg_power);

        // Ambil data lingkungan eksternal langsung dari service (tanpa HTTP loop)
        $luxValue = null; $windValue = null; $cond = null; $source = null;
        try {
            /** @var BMKGWeatherService $wx */
            $wx = App::make(BMKGWeatherService::class);
            $hourly = $wx->getHourly(-6.2, 106.8166, 24);
            $hours = $hourly['hours'] ?? [];
            if (!empty($hours)) {
                $last = end($hours);
                $luxValue = $last['light_lux'] ?? null;
                $windValue = $last['wind_speed'] ?? null;
                $cond = $last['condition'] ?? null;
                $source = $hourly['source'] ?? null;
            }
        } catch (\Throwable $e) {
            // silent fail -> N/A
        }

        $stats = [
            Stat::make('Suhu Tanah', $this->formatValue($latestData?->ground_temperature_c, '°C'))
                ->description($this->getSensorDescription('Suhu', $tempChange, $latestData?->ground_temperature_c, [15, 35]))
                ->descriptionIcon($this->getIcon($latestData?->ground_temperature_c, [15, 35], 'heroicon-m-fire', 'heroicon-m-sun', 'heroicon-m-cube-transparent'))
                ->color($this->getColor($latestData?->ground_temperature_c, [15, 35]))
                ->chart($this->getMiniChart('ground_temperature_c')),

            Stat::make('Kelembapan Tanah', $this->formatValue($latestData?->soil_moisture_pct, '%'))
                ->description($this->getSensorDescription('Tanah', $soilChange, $latestData?->soil_moisture_pct, [30, 70]))
                ->descriptionIcon($this->getIcon($latestData?->soil_moisture_pct, [30, 70], 'heroicon-m-beaker', 'heroicon-m-globe-alt', 'heroicon-m-exclamation-triangle'))
                ->color($this->getColor($latestData?->soil_moisture_pct, [30, 70]))
                ->chart($this->getMiniChart('soil_moisture_pct')),
            
            Stat::make('Irigasi Total', $this->formatValue($latestData?->irrigation_usage_total_l, ' L'))
                ->description($this->getSensorDescription('Irigasi', $irrigationChange, $latestData?->irrigation_usage_total_l, [0, max(1, ($latestData?->irrigation_usage_total_l ?? 0) * 1.2)]))
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info')
                ->chart($this->getMiniChart('irrigation_usage_total_l')),

            Stat::make('Baterai', $this->formatValue($latestData?->battery_voltage_v, ' V'))
                ->description($this->getSensorDescription('Baterai', $batteryChange, $latestData?->battery_voltage_v, [3.5, 4.2]))
                ->descriptionIcon($this->getIcon($latestData?->battery_voltage_v, [4.0, 4.2], 'heroicon-m-bolt', 'heroicon-m-battery-50', 'heroicon-m-battery-0'))
                ->color($this->getColor($latestData?->battery_voltage_v, [3.5, 4.2]))
                ->chart($this->getMiniChart('battery_voltage_v')),

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

        // Tambah statistik eksternal (Lux & Angin) kalau tersedia
        $stats[] = Stat::make('Cahaya (Lux)', $luxValue !== null ? number_format($luxValue) : 'N/A')
            ->description($cond ? ucfirst($cond).($source?" · $source":'') : 'Data eksternal')
            ->descriptionIcon('heroicon-m-sparkles')
            ->color($luxValue === null ? 'gray' : 'success');
        $stats[] = Stat::make('Angin', $windValue !== null ? number_format($windValue,1).' m/s' : 'N/A')
            ->description($windValue!==null ? '24h eksternal' : 'Tidak ada data')
            ->descriptionIcon('heroicon-m-arrow-path-rounded-square')
            ->color($windValue === null ? 'gray' : ($windValue>8 ? 'danger' : ($windValue>5 ? 'warning':'success')));

        return $stats;
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