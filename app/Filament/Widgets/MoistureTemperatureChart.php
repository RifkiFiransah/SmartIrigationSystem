<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use App\Models\Device;
use Filament\Widgets\ChartWidget;

class MoistureTemperatureChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Kelembaban dan Suhu (24 Jam Terakhir)';
    
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // Ambil data 24 jam terakhir
        $data = SensorData::with('device')
            ->where('recorded_at', '>=', now()->subHours(24))
            ->orderBy('recorded_at', 'asc')
            ->get();

        // Jika tidak ada data, return array kosong
        if ($data->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Suhu (°C)',
                    'data' => $data->pluck('temperature')->toArray(),
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => false,
                    'tension' => 0.1,
                ],
                [
                    'label' => 'Kelembaban (%)',
                    'data' => $data->pluck('humidity')->toArray(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => false,
                    'tension' => 0.1,
                ],
                [
                    'label' => 'Kelembaban Tanah (%)',
                    'data' => $data->pluck('soil_moisture')->toArray(),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => false,
                    'tension' => 0.1,
                ],
            ],
            'labels' => $data->map(function ($item) {
                return \Carbon\Carbon::parse($item->recorded_at)->format('H:i');
            })->toArray(),
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
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Nilai Sensor'
                    ]
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Waktu'
                    ]
                ]
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top'
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false
                ]
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false
            ]
        ];
    }

    public function getColumnSpan(): int | string | array
    {
        return '1/2';
    }
}
