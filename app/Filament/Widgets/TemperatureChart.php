<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TemperatureChart extends ChartWidget
{
    protected static ?string $heading = 'Temperature Trends (24h)';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'half';

    protected function getData(): array
    {
        // Get hourly data for last 24 hours
        $data = SensorData::select(
            DB::raw('HOUR(recorded_at) as hour'),
            DB::raw('AVG(temperature_c) as avg_temp'),
            DB::raw('MIN(temperature_c) as min_temp'),
            DB::raw('MAX(temperature_c) as max_temp')
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
                $avgData[] = round($hourData->avg_temp, 1);
                $minData[] = round($hourData->min_temp, 1);
                $maxData[] = round($hourData->max_temp, 1);
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
                    'backgroundColor' => 'rgba(239,68,68,0.7)', // red focus
                    'borderColor' => 'rgba(239,68,68,1)',
                    'borderWidth' => 1,
                    'maxBarThickness' => 46,
                    'barPercentage' => 0.85,
                    'categoryPercentage' => 0.9,
                    'borderRadius' => 6,
                ],
                [
                    'label' => 'Maximum',
                    'data' => $maxData,
                    'backgroundColor' => 'rgba(249,115,22,0.35)',
                    'borderColor' => 'rgba(249,115,22,0.9)',
                    'borderWidth' => 1,
                    'maxBarThickness' => 30,
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'Minimum',
                    'data' => $minData,
                    'backgroundColor' => 'rgba(14,165,233,0.35)',
                    'borderColor' => 'rgba(14,165,233,0.9)',
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
                    'beginAtZero' => false,
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
