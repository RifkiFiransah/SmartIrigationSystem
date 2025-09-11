<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SensorDataBarChart extends ChartWidget
{
    protected static ?string $heading = 'Sensor Data Overview (Last 7 Days)';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Get data for last 7 days
    $data = SensorData::select(
            DB::raw('DATE(recorded_at) as date'),
            DB::raw('AVG(temperature_c) as avg_temp'),
            DB::raw('AVG(soil_moisture_pct) as avg_moisture'),
            DB::raw('AVG(water_volume_l) as avg_volume')
        )
        ->where('recorded_at', '>=', now()->subDays(7))
        ->groupBy(DB::raw('DATE(recorded_at)'))
        ->orderBy('date')
        ->get();

        $labels = [];
        $temperatureData = [];
        $moistureData = [];
    $waterFlowData = [];

        foreach ($data as $item) {
            $labels[] = \Carbon\Carbon::parse($item->date)->format('M d');
            $temperatureData[] = round($item->avg_temp, 1);
            $moistureData[] = round($item->avg_moisture, 1);
            $waterFlowData[] = round($item->avg_volume, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Temperature (°C)',
                    'data' => $temperatureData,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.8)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 1,
                ],
                
                [
                    'label' => 'Soil Moisture (%)',
                    'data' => $moistureData,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.8)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Water Volume (L)',
                    'data' => $waterFlowData,
                    'backgroundColor' => 'rgba(153, 102, 255, 0.8)',
                    'borderColor' => 'rgba(153, 102, 255, 1)',
                    'borderWidth' => 1,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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
                ],
                'title' => [
                    'display' => true,
                        'text' => 'Daily Average: Temperature / Soil Moisture / Water Volume',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Temperature (°C) / Soil Moisture (%)',
                    ],
                    'max' => 100,
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Water Volume (L)',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
        ];
    }
}
