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
                    'borderColor' => 'rgb(14,165,233)',
                    'backgroundColor' => 'rgba(14,165,233,0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Maksimum',
                    'data' => $maxData,
                    'borderColor' => 'rgb(99,102,241)',
                    'backgroundColor' => 'rgba(99,102,241,0.1)',
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
                'title' => [ 'display' => true, 'text' => 'Ketinggian Air (cm) - 24 Jam Terakhir' ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [ 'display' => true, 'text' => 'cm' ],
                ],
                'x' => [ 'title' => [ 'display' => true, 'text' => 'Jam' ] ],
            ],
        ];
    }
}
