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
                    'label' => 'Average',
                    'data' => $avgData,
                    'backgroundColor' => 'rgba(34,197,94,0.7)', // green primary
                    'borderColor' => 'rgba(34,197,94,1)',
                    'borderWidth' => 1,
                    'maxBarThickness' => 46,
                    'barPercentage' => 0.85,
                    'categoryPercentage' => 0.9,
                    'borderRadius' => 6,
                ],
                [
                    'label' => 'Minimum',
                    'data' => $minData,
                    'backgroundColor' => 'rgba(249,115,22,0.35)', // orange soft
                    'borderColor' => 'rgba(249,115,22,0.9)',
                    'borderWidth' => 1,
                    'maxBarThickness' => 30,
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'Maximum',
                    'data' => $maxData,
                    'backgroundColor' => 'rgba(99,102,241,0.35)', // indigo soft
                    'borderColor' => 'rgba(99,102,241,0.9)',
                    'borderWidth' => 1,
                    'maxBarThickness' => 30,
                    'borderRadius' => 4,
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
                    'align' => 'center',
                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'rect',
                        'font' => [
                            'size' => 12,
                            'family' => 'Inter, system-ui, sans-serif',
                        ],
                        'color' => '#9ca3af',
                        'padding' => 20,
                        'boxWidth' => 12,
                        'boxHeight' => 12,
                    ]
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'titleColor' => '#ffffff',
                    'bodyColor' => '#ffffff',
                    'borderColor' => 'rgba(255, 255, 255, 0.1)',
                    'borderWidth' => 1,
                    'cornerRadius' => 6,
                    'padding' => 10,
                    'displayColors' => true,
                    'mode' => 'index',
                    'intersect' => false,
                ]
            ],
            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'color' => '#6b7280',
                        'font' => [
                            'size' => 11,
                            'family' => 'Inter, system-ui, sans-serif',
                        ],
                    ],
                    'border' => [
                        'display' => false,
                    ]
                ],
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'beginAtZero' => true,
                    'max' => 100,
                    'grid' => [
                        'color' => 'rgba(107, 114, 128, 0.1)',
                        'lineWidth' => 1,
                    ],
                    'ticks' => [
                        'color' => '#6b7280',
                        'font' => [
                            'size' => 11,
                            'family' => 'Inter, system-ui, sans-serif',
                        ],
                        'stepSize' => 25,
                    ],
                    'border' => [
                        'display' => false,
                    ]
                ]
            ],
            'elements' => [
                'bar' => [
                    'borderRadius' => 2,
                    'borderSkipped' => false,
                ]
            ],
            'layout' => [
                'padding' => [
                    'top' => 10,
                    'bottom' => 10,
                    'left' => 10,
                    'right' => 10,
                ]
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
        ];
    }
}
