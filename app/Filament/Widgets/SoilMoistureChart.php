<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SoilMoistureChart extends ChartWidget
{
    protected static ?string $heading = 'Soil Moisture Trends (24h)';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'half';

    protected function getData(): array
    {
        // Get hourly data for last 24 hours - only soil moisture
        $data = SensorData::select(
            DB::raw('HOUR(recorded_at) as hour'),
            DB::raw('AVG(soil_moisture_pct) as avg_moisture'),
            DB::raw('MIN(soil_moisture_pct) as min_moisture'),
            DB::raw('MAX(soil_moisture_pct) as max_moisture')
        )
        ->where('recorded_at', '>=', now()->subHours(24))
        ->groupBy(DB::raw('HOUR(recorded_at)'))
        ->orderBy('hour')
        ->get();

        $labels = [];
        $avgData = [];
        $minData = [];
        $maxData = [];

        for ($i = 0; $i < 24; $i++) {
            $labels[] = sprintf('%02d:00', $i);
            $hourData = $data->firstWhere('hour', $i);
            
            if ($hourData) {
                $avgData[] = round($hourData->avg_moisture, 1);
                $minData[] = round($hourData->min_moisture, 1);
                $maxData[] = round($hourData->max_moisture, 1);
            } else {
                $avgData[] = null;
                $minData[] = null;
                $maxData[] = null;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Average Soil Moisture',
                    'data' => $avgData,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Min Soil Moisture',
                    'data' => $minData,
                    'backgroundColor' => 'rgba(255, 159, 64, 0.1)',
                    'borderColor' => 'rgba(255, 159, 64, 0.5)',
                    'borderWidth' => 1,
                    'fill' => false,
                    'borderDash' => [5, 5],
                ],
                [
                    'label' => 'Max Soil Moisture',
                    'data' => $maxData,
                    'backgroundColor' => 'rgba(153, 102, 255, 0.1)',
                    'borderColor' => 'rgba(153, 102, 255, 0.5)',
                    'borderWidth' => 1,
                    'fill' => false,
                    'borderDash' => [5, 5],
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
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Hourly Soil Moisture Levels'
                ]
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                    'title' => [
                        'display' => true,
                        'text' => 'Soil Moisture (%)'
                    ],
                    'grid' => [
                        'color' => 'rgba(75, 192, 192, 0.1)',
                    ]
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Time (Hour)'
                    ]
                ]
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
        ];
    }
}
