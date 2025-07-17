<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class WaterFlowChart extends ChartWidget
{
    protected static ?string $heading = 'Water Flow Rate (L/h)';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'half';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        // Ambil data 7 hari terakhir
        $data = SensorData::where('recorded_at', '>=', Carbon::now()->subDays(7))
            ->orderBy('recorded_at')
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->recorded_at)->format('Y-m-d');
            })
            ->map(function ($dayData) {
                return $dayData->avg('water_flow');
            });

        $labels = $data->keys()->toArray();
        $values = $data->values()->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Average Water Flow',
                    'data' => $values,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                ]
            ],
            'labels' => array_map(function($date) {
                return Carbon::parse($date)->format('M d');
            }, $labels),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Flow Rate (L/h)'
                    ]
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Date'
                    ]
                ]
            ],
        ];
    }
}
