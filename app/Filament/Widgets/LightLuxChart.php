<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class LightLuxChart extends ChartWidget
{
    protected static ?string $heading = 'Cahaya (Lux) - 24 Jam';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'half';

    protected function getData(): array
    {
        $data = SensorData::select(
            DB::raw('HOUR(recorded_at) as hour'),
            DB::raw('AVG(light_lux) as avg_lux'),
            DB::raw('MIN(light_lux) as min_lux'),
            DB::raw('MAX(light_lux) as max_lux')
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
                $avgData[] = round($hourData->avg_lux, 0);
                $minData[] = round($hourData->min_lux, 0);
                $maxData[] = round($hourData->max_lux, 0);
            } else {
                $avgData[] = null;
                $minData[] = null;
                $maxData[] = null;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata Lux',
                    'data' => $avgData,
                    'borderColor' => 'rgb(59,130,246)',
                    'backgroundColor' => 'rgba(59,130,246,0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Maksimum',
                    'data' => $maxData,
                    'borderColor' => 'rgb(245,158,11)',
                    'backgroundColor' => 'rgba(245,158,11,0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                    'borderDash' => [5,5],
                ],
                [
                    'label' => 'Minimum',
                    'data' => $minData,
                    'borderColor' => 'rgb(16,185,129)',
                    'backgroundColor' => 'rgba(16,185,129,0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                    'borderDash' => [5,5],
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
                'legend' => [ 'display' => true, 'position' => 'top' ],
                'title' => [ 'display' => true, 'text' => 'Cahaya (Lux) - 24 Jam Terakhir' ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [ 'display' => true, 'text' => 'Lux' ],
                ],
                'x' => [ 'title' => [ 'display' => true, 'text' => 'Jam' ] ],
            ],
        ];
    }
}
