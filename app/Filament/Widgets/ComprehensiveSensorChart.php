<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ComprehensiveSensorChart extends ChartWidget
{
    protected static ?string $heading = 'Semua Data Sensor - 24 Jam Terakhir';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = SensorData::select(
            DB::raw('HOUR(recorded_at) as hour'),
            DB::raw('AVG(temperature_c) as avg_temp'),
            DB::raw('AVG(soil_moisture_pct) as avg_soil'),
            DB::raw('AVG(water_volume_l) as avg_volume'),
            DB::raw('AVG(light_lux) as avg_light'),
            DB::raw('AVG(wind_speed_ms) as avg_wind'),
            DB::raw('AVG(water_height_cm) as avg_height'),
            DB::raw('AVG(ina226_power_mw) as avg_power')
        )
        ->where('recorded_at', '>=', now()->subHours(24))
        ->groupBy(DB::raw('HOUR(recorded_at)'))
        ->orderBy('hour')
        ->get();

        $labels = [];
        $tempData = [];
        $soilData = [];
        $volumeData = [];
        $lightData = [];
        $windData = [];
        $heightData = [];
        $powerData = [];

        for ($i = 0; $i < 24; $i++) {
            $labels[] = sprintf('%02d:00', $i);
            $hourData = $data->firstWhere('hour', $i);
            
            if ($hourData) {
                $tempData[] = round($hourData->avg_temp, 1);
                $soilData[] = round($hourData->avg_soil, 1);
                $volumeData[] = round($hourData->avg_volume, 2);
                $lightData[] = round($hourData->avg_light / 1000, 1); // Convert to K-Lux
                $windData[] = round($hourData->avg_wind, 2);
                $heightData[] = round($hourData->avg_height, 1);
                $powerData[] = round($hourData->avg_power / 100, 2); // Convert to 100mW units
            } else {
                $tempData[] = null;
                $soilData[] = null;
                $volumeData[] = null;
                $lightData[] = null;
                $windData[] = null;
                $heightData[] = null;
                $powerData[] = null;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Suhu (°C)',
                    'data' => $tempData,
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Kelembapan Tanah (%)',
                    'data' => $soilData,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Volume Air (L)',
                    'data' => $volumeData,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'Cahaya (K-Lux)',
                    'data' => $lightData,
                    'borderColor' => 'rgb(245, 158, 11)',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'Angin (m/s)',
                    'data' => $windData,
                    'borderColor' => 'rgb(99, 102, 241)',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Tinggi Air (cm)',
                    'data' => $heightData,
                    'borderColor' => 'rgb(14, 165, 233)',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Daya (100mW)',
                    'data' => $powerData,
                    'borderColor' => 'rgb(234, 88, 12)',
                    'backgroundColor' => 'rgba(234, 88, 12, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'line',
                        'font' => ['size' => 11],
                        'padding' => 15,
                        'boxWidth' => 25,
                    ]
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Monitoring Sensor IoT - Semua Parameter (24 Jam)',
                    'font' => ['size' => 14, 'weight' => 'bold'],
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'title' => ['display' => true, 'text' => 'Jam'],
                    'grid' => ['display' => false],
                ],
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => ['display' => true, 'text' => '°C / % / m/s / cm'],
                    'beginAtZero' => true,
                    'max' => 100,
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => ['display' => true, 'text' => 'L / K-Lux / 100mW'],
                    'beginAtZero' => true,
                    'grid' => ['drawOnChartArea' => false],
                ],
            ],
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
        ];
    }
}
