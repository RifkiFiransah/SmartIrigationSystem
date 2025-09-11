<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class WindSpeedChart extends ChartWidget
{
    protected static ?string $heading = 'Kecepatan Angin (m/s) - 24 Jam';
    protected static ?int $sort = 7;
    protected int | string | array $columnSpan = 'half';

    protected function getData(): array
    {
        $data = SensorData::select(
            DB::raw('HOUR(recorded_at) as hour'),
            DB::raw('AVG(wind_speed_ms) as avg_ws'),
            DB::raw('MIN(wind_speed_ms) as min_ws'),
            DB::raw('MAX(wind_speed_ms) as max_ws')
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
                $avgData[] = round($hourData->avg_ws, 2);
                $minData[] = round($hourData->min_ws, 2);
                $maxData[] = round($hourData->max_ws, 2);
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
                    'borderColor' => 'rgb(99,102,241)',
                    'backgroundColor' => 'rgba(99,102,241,0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Maksimum',
                    'data' => $maxData,
                    'borderColor' => 'rgb(251,146,60)',
                    'backgroundColor' => 'rgba(251,146,60,0.1)',
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
                'title' => [ 'display' => true, 'text' => 'Kecepatan Angin (m/s) - 24 Jam Terakhir' ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [ 'display' => true, 'text' => 'm/s' ],
                ],
                'x' => [ 'title' => [ 'display' => true, 'text' => 'Jam' ] ],
            ],
        ];
    }
}
