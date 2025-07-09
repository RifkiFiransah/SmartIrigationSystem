<?php

namespace App\Filament\Resources\SensorDataResource\Widgets;

use App\Models\SensorData;
use Filament\Widgets\ChartWidget;

class SensorDataChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Data Sensor';

    protected function getData(): array
    {
        $data = SensorData::orderby('recorded_at', 'desc')
            ->take(30)
            ->get()
            ->map(function ($sensorData) {
                return [
                    'x' => $sensorData->recorded_at->format('Y-m-d H:i'),
                    'y' => $sensorData->temperature,
                ];
            });
        return [
            'datasets' => [
                [
                    'label' => 'Suhu (°C)',
                    'data' => $data->toArray(),
                    'borderColor' => '#FF5733',
                    'backgroundColor' => 'rgba(255, 87, 51, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => 'Kelembapan (%)',
                    'data' => $data->map(function ($item) {
                        return ['x' => $item['x'], 'y' => $item['y'] * 0.8]; // Example transformation
                    })->toArray(),
                    'borderColor' => '#33FF57',
                    'backgroundColor' => 'rgba(51, 255, 87, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => 'Kelembapan Tanah (%)',
                    'data' => $data->map(function ($item) {
                        return ['x' => $item['x'], 'y' => $item['y'] * 0.6]; // Example transformation
                    })->toArray(),
                    'borderColor' => '#3357FF',
                    'backgroundColor' => 'rgba(51, 87, 255, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => 'Aliran Air (L/min)',
                    'data' => $data->map(function ($item) {
                        return ['x' => $item['x'], 'y' => $item['y'] * 0.4]; // Example transformation
                    })->toArray(),
                    'borderColor' => '#FF33A1',
                    'backgroundColor' => 'rgba(255, 51, 161, 0.2)',
                    'fill' => true,
                ]
            ],
            'labels' => $data->pluck('x')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
