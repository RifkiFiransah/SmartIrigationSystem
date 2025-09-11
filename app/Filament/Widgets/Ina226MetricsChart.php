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
                    'label' => 'Bus Voltage (V)',
                    'data' => $vData,
                    'borderColor' => 'rgb(59,130,246)',
                    'backgroundColor' => 'rgba(59,130,246,0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'Current (mA)',
                    'data' => $iData,
                    'borderColor' => 'rgb(16,185,129)',
                    'backgroundColor' => 'rgba(16,185,129,0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Power (mW)',
                    'data' => $pData,
                    'borderColor' => 'rgb(234,88,12)',
                    'backgroundColor' => 'rgba(234,88,12,0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
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
                'title' => [ 'display' => true, 'text' => 'INA226: Tegangan/Arus/Daya (24 Jam Terakhir)' ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [ 'display' => true, 'text' => 'Arus (mA)' ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [ 'display' => true, 'text' => 'Tegangan (V) / Daya (mW)' ],
                    'grid' => [ 'drawOnChartArea' => false ],
                ],
                'x' => [ 'title' => [ 'display' => true, 'text' => 'Jam' ] ],
            ],
        ];
    }
}
