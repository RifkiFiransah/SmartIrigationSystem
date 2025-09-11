<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class RealtimeStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Real-time System Status';
    protected static ?int $sort = 999;
    protected int | string | array $columnSpan = 'full';
    
    // Refresh setiap 30 detik
    protected static ?string $pollingInterval = '30s';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        // Ambil data terbaru dari masing-masing status
        $latestData = SensorData::where('recorded_at', '>=', Carbon::now()->subHours(1))
            ->get();

        $statusCounts = $latestData->countBy('status');

        return [
            'datasets' => [
                [
                    'label' => 'System Status',
                    'data' => [
                        $statusCounts->get('normal', 0),
                        $statusCounts->get('alert', 0),
                        $statusCounts->get('critical', 0),
                    ],
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',   // Green for normal
                        'rgba(251, 191, 36, 0.8)',  // Yellow for alert
                        'rgba(239, 68, 68, 0.8)',   // Red for critical
                    ],
                    'borderColor' => [
                        'rgba(34, 197, 94, 1)',
                        'rgba(251, 191, 36, 1)',
                        'rgba(239, 68, 68, 1)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['Normal', 'Alert', 'Critical'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'title' => [
                    'display' => true,
                    'text' => 'System Status Distribution (Last Hour)'
                ]
            ],
        ];
    }
}
