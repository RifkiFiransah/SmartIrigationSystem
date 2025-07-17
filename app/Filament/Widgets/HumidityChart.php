<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class HumidityChart extends ChartWidget
{
    protected static ?string $heading = 'Humidity Trends (24h)';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'half';

    protected function getData(): array
    {
        // Get hourly data for last 24 hours - only humidity
        $data = SensorData::select(
            DB::raw('HOUR(recorded_at) as hour'),
            DB::raw('AVG(humidity) as avg_humidity'),
            DB::raw('MIN(humidity) as min_humidity'),
            DB::raw('MAX(humidity) as max_humidity')
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
                $avgData[] = round($hourData->avg_humidity, 1);
                $minData[] = round($hourData->min_humidity, 1);
                $maxData[] = round($hourData->max_humidity, 1);
            } else {
                $avgData[] = null;
                $minData[] = null;
                $maxData[] = null;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Average Humidity',
                    'data' => $avgData,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Min Humidity',
                    'data' => $minData,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.1)',
                    'borderColor' => 'rgba(75, 192, 192, 0.5)',
                    'borderWidth' => 1,
                    'fill' => false,
                    'borderDash' => [5, 5],
                ],
                [
                    'label' => 'Max Humidity',
                    'data' => $maxData,
                    'backgroundColor' => 'rgba(255, 205, 86, 0.1)',
                    'borderColor' => 'rgba(255, 205, 86, 0.5)',
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
                    'text' => 'Humidity & Moisture (%) - Last 24 Hours',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                    'title' => [
                        'display' => true,
                        'text' => 'Percentage (%)',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Hour',
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
