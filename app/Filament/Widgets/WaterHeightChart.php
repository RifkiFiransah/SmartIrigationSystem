<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class WaterHeightChart extends ChartWidget
{
    protected static ?string $heading = 'Ketinggian Air (cm) - 24 Jam';
    protected static ?int $sort = 8;
    protected int | string | array $columnSpan = 'half';

    protected function getData(): array
    {
        $data = SensorData::select(
            DB::raw('HOUR(recorded_at) as hour'),
            DB::raw('AVG(water_height_cm) as avg_h'),
            DB::raw('MIN(water_height_cm) as min_h'),
            DB::raw('MAX(water_height_cm) as max_h')
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
                $avgData[] = round($hourData->avg_h, 0);
                $minData[] = round($hourData->min_h, 0);
                $maxData[] = round($hourData->max_h, 0);
            } else {
                $avgData[] = null;
                $minData[] = null;
                $maxData[] = null;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata',
                    'data' => $avgData,
                    'backgroundColor' => 'rgba(14,165,233,0.7)',
                    'borderColor' => 'rgba(14,165,233,1)',
                    'borderWidth' => 1,
                    'maxBarThickness' => 46,
                    'borderRadius' => 6,
                ],
                [
                    'label' => 'Maksimum',
                    'data' => $maxData,
                    'backgroundColor' => 'rgba(99,102,241,0.35)',
                    'borderColor' => 'rgba(99,102,241,0.9)',
                    'borderWidth' => 1,
                    'maxBarThickness' => 30,
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'Minimum',
                    'data' => $minData,
                    'backgroundColor' => 'rgba(16,185,129,0.35)',
                    'borderColor' => 'rgba(16,185,129,0.9)',
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
                        'font' => [ 'size' => 12, 'family' => 'Inter, system-ui, sans-serif' ],
                        'color' => '#9ca3af',
                        'padding' => 20,
                        'boxWidth' => 12,
                        'boxHeight' => 12,
                    ]
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(0,0,0,0.8)',
                    'titleColor' => '#ffffff',
                    'bodyColor' => '#ffffff',
                    'borderColor' => 'rgba(255,255,255,0.1)',
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
                    'grid' => [ 'display' => false ],
                    'ticks' => [
                        'color' => '#6b7280',
                        'font' => [ 'size' => 11, 'family' => 'Inter, system-ui, sans-serif' ],
                    ],
                    'border' => [ 'display' => false ],
                ],
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'beginAtZero' => true,
                    'grid' => [ 'color' => 'rgba(107,114,128,0.1)', 'lineWidth' => 1 ],
                    'ticks' => [
                        'color' => '#6b7280',
                        'font' => [ 'size' => 11, 'family' => 'Inter, system-ui, sans-serif' ],
                    ],
                    'border' => [ 'display' => false ],
                ]
            ],
            'elements' => [ 'bar' => [ 'borderRadius' => 2, 'borderSkipped' => false ] ],
            'layout' => [ 'padding' => [ 'top' => 10, 'bottom' => 10, 'left' => 10, 'right' => 10 ] ],
            'interaction' => [ 'intersect' => false, 'mode' => 'index' ],
            'animation' => [ 'duration' => 500, 'easing' => 'easeInOutQuart' ]
        ];
    }
}
