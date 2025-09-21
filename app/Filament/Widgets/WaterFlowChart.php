<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class WaterFlowChart extends ChartWidget
{
    protected static ?string $heading = 'Water Volume (L)';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'half';
    // Menyimpan hasil perhitungan dinamis min & max untuk axis
    protected ?float $computedMin = null;
    protected ?float $computedMax = null;

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
        return $dayData->avg('water_volume_l');
            });

        $labels = $data->keys()->toArray();
        $values = $data->values()->toArray();

        // Hitung perubahan hari-ke-hari (delta) untuk visual naik turun
        $deltas = [];
        for ($i = 0; $i < count($values); $i++) {
            if ($i === 0 || $values[$i] === null || $values[$i-1] === null) {
                $deltas[] = null; // tidak ada delta hari pertama / data kosong
            } else {
                $deltas[] = round($values[$i] - $values[$i-1], 2);
            }
        }

        // Simpan min/max untuk opsi (akan dipakai di getOptions melalui properti transient)
        $this->computedMin = null;
        $this->computedMax = null;
        if (!empty($values)) {
            $filtered = array_filter($values, fn($v) => $v !== null);
            if (!empty($filtered)) {
                $min = min($filtered);
                $max = max($filtered);
                if ($min === $max) {
                    // Kalau flat line, beri sedikit variasi supaya terlihat
                    $min -= 1; $max += 1;
                }
                $range = max(0.0001, $max - $min);
                $pad = $range * 0.15; // 15% padding
                $this->computedMin = max(0, $min - $pad);
                $this->computedMax = $max + $pad;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Average Water Volume',
                    'data' => $values,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.2,
                ]
                ,[
                    'type' => 'bar',
                    'label' => 'Δ Perubahan (L)',
                    'data' => $deltas,
                    'backgroundColor' => array_map(function($d){
                        if ($d === null) return 'rgba(0,0,0,0)';
                        return $d >= 0 ? 'rgba(34,197,94,0.6)' : 'rgba(239,68,68,0.6)';
                    }, $deltas),
                    'borderColor' => array_map(function($d){
                        if ($d === null) return 'rgba(0,0,0,0)';
                        return $d >= 0 ? 'rgba(34,197,94,1)' : 'rgba(239,68,68,1)';
                    }, $deltas),
                    'borderWidth' => 1,
                    'yAxisID' => 'y',
                    'order' => 2,
                    'maxBarThickness' => 36,
                    'barPercentage' => 0.6,
                    'categoryPercentage' => 0.7,
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
                    'beginAtZero' => false,
                    'min' => $this->computedMin ?? null,
                    'max' => $this->computedMax ?? null,
                    'title' => [ 'display' => true, 'text' => 'Volume (L)' ],
                    'grid' => [ 'color' => 'rgba(107,114,128,0.12)' ],
                    'ticks' => [ 'color' => '#6b7280' ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Date'
                    ]
                ]
            ],
            'interaction' => [ 'intersect' => false, 'mode' => 'index' ],
        ];
    }
}
