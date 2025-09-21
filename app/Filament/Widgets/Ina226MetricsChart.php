<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class Ina226MetricsChart extends ChartWidget
{
    protected static ?string $heading = 'INA226 Metrics (V / mA / mW) - 24 Jam';
    protected static ?int $sort = 9;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = SensorData::select(
            DB::raw('HOUR(recorded_at) as hour'),
            DB::raw('AVG(ina226_bus_voltage_v) as v_avg'),
            DB::raw('AVG(ina226_current_ma) as i_avg'),
            DB::raw('AVG(ina226_power_mw) as p_avg')
        )
        ->where('recorded_at', '>=', now()->subHours(24))
        ->groupBy(DB::raw('HOUR(recorded_at)'))
        ->orderBy('hour')
        ->get();

        $labels = [];
        $vData = [];
        $iData = [];
        $pData = [];

        for ($i = 0; $i < 24; $i++) {
            $labels[] = sprintf('%02d:00', $i);
            $hourData = $data->firstWhere('hour', $i);
            if ($hourData) {
                $vData[] = round($hourData->v_avg, 3);
                $iData[] = round($hourData->i_avg, 3);
                $pData[] = round($hourData->p_avg, 3);
            } else {
                $vData[] = null;
                $iData[] = null;
                $pData[] = null;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Current (mA)',
                    'data' => $iData,
                    'backgroundColor' => 'rgba(16,185,129,0.7)',
                    'borderColor' => 'rgba(16,185,129,1)',
                    'borderWidth' => 1,
                    'maxBarThickness' => 46,
                    'borderRadius' => 6,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Bus Voltage (V)',
                    'data' => $vData,
                    'backgroundColor' => 'rgba(59,130,246,0.35)',
                    'borderColor' => 'rgba(59,130,246,0.9)',
                    'borderWidth' => 1,
                    'maxBarThickness' => 30,
                    'borderRadius' => 4,
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'Power (mW)',
                    'data' => $pData,
                    'backgroundColor' => 'rgba(234,88,12,0.35)',
                    'borderColor' => 'rgba(234,88,12,0.9)',
                    'borderWidth' => 1,
                    'maxBarThickness' => 30,
                    'borderRadius' => 4,
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
                'title' => [ 'display' => true, 'text' => 'INA226: Tegangan / Arus / Daya (24 Jam Terakhir)' ],
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
                    'title' => [ 'display' => true, 'text' => 'Arus (mA)' ],
                    'grid' => [ 'color' => 'rgba(107,114,128,0.1)', 'lineWidth' => 1 ],
                    'ticks' => [
                        'color' => '#6b7280',
                        'font' => [ 'size' => 11, 'family' => 'Inter, system-ui, sans-serif' ],
                    ],
                    'border' => [ 'display' => false ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [ 'display' => true, 'text' => 'Tegangan (V) / Daya (mW)' ],
                    'grid' => [ 'drawOnChartArea' => false ],
                    'ticks' => [
                        'color' => '#6b7280',
                        'font' => [ 'size' => 11, 'family' => 'Inter, system-ui, sans-serif' ],
                    ],
                    'border' => [ 'display' => false ],
                ],
            ],
            'elements' => [ 'bar' => [ 'borderRadius' => 2, 'borderSkipped' => false ] ],
            'layout' => [ 'padding' => [ 'top' => 10, 'bottom' => 10, 'left' => 10, 'right' => 10 ] ],
            'interaction' => [ 'intersect' => false, 'mode' => 'index' ],
            'animation' => [ 'duration' => 500, 'easing' => 'easeInOutQuart' ]
        ];
    }
}
