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
            DB::raw('AVG(temperature) as avg_temp'),
            DB::raw('AVG(humidity) as avg_humidity'),
            DB::raw('AVG(soil_moisture) as avg_moisture'),
            DB::raw('AVG(water_flow) as avg_flow')
        )
        ->where('recorded_at', '>=', now()->subDays(7))
        ->groupBy(DB::raw('DATE(recorded_at)'))
        ->orderBy('date')
        ->get();

        $labels = [];
        $temperatureData = [];
        $humidityData = [];
        $moistureData = [];
        $waterFlowData = [];

        foreach ($data as $item) {
            $labels[] = \Carbon\Carbon::parse($item->date)->format('M d');
            $temperatureData[] = round($item->avg_temp, 1);
            $humidityData[] = round($item->avg_humidity, 1);
            $moistureData[] = round($item->avg_moisture, 1);
            $waterFlowData[] = round($item->avg_flow, 1);
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
                    'label' => 'Humidity (%)',
                    'data' => $humidityData,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.8)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
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
                    'label' => 'Water Flow (L/h)',
                    'data' => $waterFlowData,
                    'backgroundColor' => 'rgba(153, 102, 255, 0.8)',
                    'borderColor' => 'rgba(153, 102, 255, 1)',
                    'borderWidth' => 1,
                    'yAxisID' => 'y1', // Different scale for water flow
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
                    'text' => 'Daily Average Sensor Readings',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Temperature (°C) / Humidity (%) / Soil Moisture (%)',
                    ],
                    'max' => 100,
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Water Flow (L/h)',
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
