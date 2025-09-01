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

        // Statistik untuk perbandingan
        $avgLast24h = SensorData::where('recorded_at', '>=', now()->subHours(24))
            ->selectRaw('
                AVG(temperature) as avg_temp,
                AVG(humidity) as avg_humidity,
                AVG(soil_moisture) as avg_soil_moisture,
                AVG(water_flow) as avg_water_flow
            ')
            ->first();

        // Hitung perubahan dari rata-rata 24 jam
        $tempChange = $latestData && $avgLast24h && $avgLast24h->avg_temp 
            ? (($latestData->temperature - $avgLast24h->avg_temp) / $avgLast24h->avg_temp) * 100 
            : 0;

        $soilChange = $latestData && $avgLast24h && $avgLast24h->avg_soil_moisture 
            ? (($latestData->soil_moisture - $avgLast24h->avg_soil_moisture) / $avgLast24h->avg_soil_moisture) * 100 
            : 0;

        $humidityChange = $latestData && $avgLast24h && $avgLast24h->avg_humidity 
            ? (($latestData->humidity - $avgLast24h->avg_humidity) / $avgLast24h->avg_humidity) * 100 
            : 0;

        $flowChange = $latestData && $avgLast24h && $avgLast24h->avg_water_flow 
            ? (($latestData->water_flow - $avgLast24h->avg_water_flow) / $avgLast24h->avg_water_flow) * 100 
            : 0;

        return [
            Stat::make('Suhu Terkini', $this->formatTemperature($latestData))
                ->description($this->getTemperatureDescription($latestData, $tempChange))
                ->descriptionIcon($this->getTemperatureIcon($latestData))
                ->color($this->getTemperatureColor($latestData))
                ->chart($this->getTemperatureChart())
                ->extraAttributes([
                    'class' => 'relative overflow-hidden',
                ]),

            Stat::make('Kelembaban Tanah', $this->formatSoilMoisture($latestData))
                ->description($this->getSoilMoistureDescription($latestData, $soilChange))
                ->descriptionIcon($this->getSoilMoistureIcon($latestData))
                ->color($this->getSoilMoistureColor($latestData))
                ->chart($this->getSoilMoistureChart())
                ->extraAttributes([
                    'class' => 'relative overflow-hidden',
                ]),
            
            Stat::make('Kelembaban Udara', $this->formatHumidity($latestData))
                ->description($this->getHumidityDescription($latestData, $humidityChange))
                ->descriptionIcon($this->getHumidityIcon($latestData))
                ->color($this->getHumidityColor($latestData))
                ->chart($this->getHumidityChart())
                ->extraAttributes([
                    'class' => 'relative overflow-hidden',
                ]),

            Stat::make('Aliran Air', $this->formatWaterFlow($latestData))
                ->description($this->getWaterFlowDescription($latestData, $flowChange))
                ->descriptionIcon($this->getWaterFlowIcon($latestData))
                ->color($this->getWaterFlowColor($latestData))
                ->chart($this->getWaterFlowChart())
                ->extraAttributes([
                    'class' => 'relative overflow-hidden',
                ]),

            // Stat::make('Status System', $this->getSystemStatus())
            //     ->description($this->getSystemDescription())
            //     ->descriptionIcon($this->getSystemIcon())
            //     ->color($this->getSystemColor())
            //     ->extraAttributes([
            //         'class' => 'relative overflow-hidden',
            //     ]),
        ];
    }

    // Temperature methods
    private function formatTemperature($latestData): string
    {
        return $latestData ? number_format($latestData->temperature, 1) . '°C' : 'N/A';
    }

    private function getTemperatureDescription($latestData, $change): string
    {
        if (!$latestData) return 'Tidak ada data';
        
        $changeText = '';
        if (abs($change) > 0.1) {
            $changeText = sprintf(' (%s%.1f%%)', $change > 0 ? '+' : '', $change);
        }
        
        return 'Update: ' . $latestData->recorded_at->diffForHumans() . $changeText;
    }

    private function getTemperatureIcon($latestData): string
    {
        if (!$latestData) return 'heroicon-m-question-mark-circle';
        
        return match (true) {
            $latestData->temperature > 35 => 'heroicon-m-fire',
            $latestData->temperature < 15 => 'heroicon-m-cube-transparent',
            default => 'heroicon-m-sun'
        };
    }

    private function getTemperatureColor($latestData): string
    {
        if (!$latestData) return 'gray';
        
        return match (true) {
            $latestData->temperature > 38 => 'danger',
            $latestData->temperature > 35 => 'warning',
            $latestData->temperature < 10 => 'info',
            default => 'success'
        };
    }

    // Soil Moisture methods
    private function formatSoilMoisture($latestData): string
    {
        return $latestData ? number_format($latestData->soil_moisture, 1) . '%' : 'N/A';
    }

    private function getSoilMoistureDescription($latestData, $change): string
    {
        if (!$latestData) return 'Tidak ada data';
        
        $status = $this->getSoilMoistureStatus($latestData->soil_moisture);
        $changeText = '';
        if (abs($change) > 0.1) {
            $changeText = sprintf(' (%s%.1f%%)', $change > 0 ? '+' : '', $change);
        }
        
        return $status . $changeText;
    }

    private function getSoilMoistureStatus($value): string
    {
        return match (true) {
            $value < 20 => 'Sangat Kering',
            $value < 40 => 'Kering', 
            $value < 60 => 'Optimal',
            $value < 80 => 'Lembab',
            default => 'Sangat Lembab'
        };
    }

    private function getSoilMoistureIcon($latestData): string
    {
        if (!$latestData) return 'heroicon-m-question-mark-circle';
        
        return match (true) {
            $latestData->soil_moisture < 25 => 'heroicon-m-exclamation-triangle',
            $latestData->soil_moisture > 75 => 'heroicon-m-beaker',
            default => 'heroicon-m-globe-alt'
        };
    }

    private function getSoilMoistureColor($latestData): string
    {
        if (!$latestData) return 'gray';
        
        return match (true) {
            $latestData->soil_moisture < 20 => 'danger',
            $latestData->soil_moisture < 30 => 'warning',
            $latestData->soil_moisture > 80 => 'info',
            default => 'success'
        };
    }

    // Humidity methods
    private function formatHumidity($latestData): string
    {
        return $latestData ? number_format($latestData->humidity, 1) . '%' : 'N/A';
    }

    private function getHumidityDescription($latestData, $change): string
    {
        if (!$latestData) return 'Tidak ada data';
        
        $status = $this->getHumidityStatus($latestData->humidity);
        $changeText = '';
        if (abs($change) > 0.1) {
            $changeText = sprintf(' (%s%.1f%%)', $change > 0 ? '+' : '', $change);
        }
        
        return $status . $changeText;
    }

    private function getHumidityStatus($value): string
    {
        return match (true) {
            $value < 30 => 'Sangat Rendah',
            $value < 50 => 'Rendah',
            $value < 70 => 'Normal',
            $value < 85 => 'Tinggi',
            default => 'Sangat Tinggi'
        };
    }

    private function getHumidityIcon($latestData): string
    {
        if (!$latestData) return 'heroicon-m-question-mark-circle';
        
        return match (true) {
            $latestData->humidity < 30 => 'heroicon-m-sun',
            $latestData->humidity > 80 => 'heroicon-m-cloud',
            default => 'heroicon-m-cloud'
        };
    }

    private function getHumidityColor($latestData): string
    {
        if (!$latestData) return 'gray';
        
        return match (true) {
            $latestData->humidity < 25 => 'warning',
            $latestData->humidity > 90 => 'info',
            default => 'success'
        };
    }

    // Water Flow methods
    private function formatWaterFlow($latestData): string
    {
        return $latestData ? number_format($latestData->water_flow, 1) . ' L/h' : 'N/A';
    }

    private function getWaterFlowDescription($latestData, $change): string
    {
        if (!$latestData) return 'Tidak ada data';
        
        $status = $this->getWaterFlowStatus($latestData->water_flow);
        $changeText = '';
        if (abs($change) > 0.1) {
            $changeText = sprintf(' (%s%.1f%%)', $change > 0 ? '+' : '', $change);
        }
        
        return $status . $changeText;
    }

    private function getWaterFlowStatus($value): string
    {
        return match (true) {
            $value == 0 => 'Tidak Ada Aliran',
            $value < 50 => 'Aliran Rendah',
            $value < 150 => 'Aliran Normal',
            $value < 300 => 'Aliran Tinggi',
            default => 'Aliran Sangat Tinggi'
        };
    }

    private function getWaterFlowIcon($latestData): string
    {
        if (!$latestData) return 'heroicon-m-question-mark-circle';
        
        return match (true) {
            $latestData->water_flow == 0 => 'heroicon-m-x-circle',
            $latestData->water_flow < 50 => 'heroicon-m-minus-circle',
            $latestData->water_flow > 200 => 'heroicon-m-arrow-up',
            default => 'heroicon-m-arrow-right'
        };
    }

    private function getWaterFlowColor($latestData): string
    {
        if (!$latestData) return 'gray';
        
        return match (true) {
            $latestData->water_flow == 0 => 'danger',
            $latestData->water_flow < 25 => 'warning',
            $latestData->water_flow > 400 => 'info',
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
    private function getTemperatureChart(): array
    {
        return $this->getMiniChart('temperature');
    }

    private function getSoilMoistureChart(): array
    {
        return $this->getMiniChart('soil_moisture');
    }

    private function getHumidityChart(): array
    {
        return $this->getMiniChart('humidity');
    }

    private function getWaterFlowChart(): array
    {
        return $this->getMiniChart('water_flow');
    }

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