<?php

namespace App\Filament\Widgets;

// use App\Models\SensorData;
// use App\Models\Device;
// use Filament\Widgets\ChartWidget;

// class MoistureTemperatureChart extends ChartWidget
// {
//     protected static ?string $heading = 'Grafik Data Sensor (24 Jam Terakhir)';
    
//     protected static ?int $sort = 2;
//     protected int | string | array $columnSpan = 'full';

//     protected function getData(): array
//     {
//         // Ambil data 24 jam terakhir dengan interval yang lebih teratur
//         $data = SensorData::with('device')
//             ->where('recorded_at', '>=', now()->subHours(24))
//             ->orderBy('recorded_at', 'asc')
//             ->get();

//         // Jika tidak ada data, return array kosong dengan pesan
//         if ($data->isEmpty()) {
//             return [
//                 'datasets' => [
//                     [
//                         'label' => 'No Data Available',
//                         'data' => [0],
//                         'backgroundColor' => '#6b7280',
//                     ]
//                 ],
//                 'labels' => ['No Data'],
//             ];
//         }

//         // Group data by 2-hour intervals untuk mengurangi kepadatan
//     $groupedData = $data->groupBy(function ($item) {
//             $hour = \Carbon\Carbon::parse($item->recorded_at)->hour;
//             // Group by 2-hour intervals
//             $interval = floor($hour / 2) * 2;
//             return sprintf('%02d:00', $interval);
//     })->map(function ($hourData) {
//             return [
//         'temperature' => round($hourData->avg('temperature_c'), 1),
//         'soil_moisture' => round($hourData->avg('soil_moisture_pct'), 1),
//         'water_volume' => round($hourData->avg('water_volume_l'), 2),
//                 'time' => $hourData->first()->recorded_at,
//             ];
//         })->values();

//         return [
//             'datasets' => [
//                 [
//                     'label' => 'Temperature (°C)',
//                     'data' => $groupedData->pluck('temperature')->toArray(),
//                     'backgroundColor' => '#ef4444',
//                     'borderColor' => '#ef4444',
//                     'borderWidth' => 0,
//                 ],
                
//                 [
//                     'label' => 'Soil Moisture (%)',
//                     'data' => $groupedData->pluck('soil_moisture')->toArray(),
//                     'backgroundColor' => '#10b981',
//                     'borderColor' => '#10b981',
//                     'borderWidth' => 0,
//                 ],
//                 [
//                     'label' => 'Water Volume (L)',
//                     'data' => $groupedData->pluck('water_volume')->toArray(),
//                     'backgroundColor' => '#8b5cf6',
//                     'borderColor' => '#8b5cf6',
//                     'borderWidth' => 0,
//                     'yAxisID' => 'y1',
//                 ],
//             ],
//             'labels' => $groupedData->map(function ($item) {
//                 return \Carbon\Carbon::parse($item['time'])->format('H:i');
//             })->toArray(),
//         ];
//     }

//     protected function getType(): string
//     {
//         return 'bar';
//     }

//     protected function getOptions(): array
//     {
//         return [
//             'responsive' => true,
//             'maintainAspectRatio' => false,
//             'plugins' => [
//                 'legend' => [
//                     'display' => true,
//                     'position' => 'top',
//                     'align' => 'center',
//                     'labels' => [
//                         'usePointStyle' => true,
//                         'pointStyle' => 'rect',
//                         'font' => [
//                             'size' => 12,
//                             'family' => 'Inter, system-ui, sans-serif',
//                         ],
//                         'color' => '#9ca3af',
//                         'padding' => 20,
//                         'boxWidth' => 12,
//                         'boxHeight' => 12,
//                     ]
//                 ],
//                 'tooltip' => [
//                     'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
//                     'titleColor' => '#ffffff',
//                     'bodyColor' => '#ffffff',
//                     'borderColor' => 'rgba(255, 255, 255, 0.1)',
//                     'borderWidth' => 1,
//                     'cornerRadius' => 6,
//                     'padding' => 10,
//                     'displayColors' => true,
//                     'mode' => 'index',
//                     'intersect' => false,
//                 ]
//             ],
//             'scales' => [
//                 'x' => [
//                     'grid' => [
//                         'display' => false,
//                     ],
//                     'ticks' => [
//                         'color' => '#6b7280',
//                         'font' => [
//                             'size' => 11,
//                             'family' => 'Inter, system-ui, sans-serif',
//                         ],
//                     ],
//                     'border' => [
//                         'display' => false,
//                     ]
//                 ],
//                 'y' => [
//                     'type' => 'linear',
//                     'display' => true,
//                     'position' => 'left',
//                     'beginAtZero' => true,
//                     'max' => 100,
//                     'grid' => [
//                         'color' => 'rgba(107, 114, 128, 0.1)',
//                         'lineWidth' => 1,
//                     ],
//                     'ticks' => [
//                         'color' => '#6b7280',
//                         'font' => [
//                             'size' => 11,
//                             'family' => 'Inter, system-ui, sans-serif',
//                         ],
//                         'stepSize' => 25,
//                     ],
//                     'border' => [
//                         'display' => false,
//                     ]
//                 ],
//                 'y1' => [
//                     'type' => 'linear',
//                     'display' => true,
//                     'position' => 'right',
//                     'beginAtZero' => true,
//                     'max' => 200,
//                     'grid' => [
//                         'drawOnChartArea' => false,
//                     ],
//                     'ticks' => [
//                         'color' => '#6b7280',
//                         'font' => [
//                             'size' => 11,
//                             'family' => 'Inter, system-ui, sans-serif',
//                         ],
//                         'stepSize' => 50,
//                     ],
//                     'border' => [
//                         'display' => false,
//                     ]
//                 ]
//             ],
//             'elements' => [
//                 'bar' => [
//                     'backgroundColor' => 'rgba(0, 0, 0, 0.1)',
//                     'borderRadius' => 2,
//                     'borderSkipped' => false,
//                 ]
//             ],
//             'layout' => [
//                 'padding' => [
//                     'top' => 10,
//                     'bottom' => 10,
//                     'left' => 10,
//                     'right' => 10,
//                 ]
//             ],
//             'interaction' => [
//                 'intersect' => false,
//                 'mode' => 'index',
//             ],
//             'animation' => [
//                 'duration' => 500,
//                 'easing' => 'easeInOutQuart',
//             ]
//         ];
//     }

//     // Method untuk refresh data secara otomatis
//     protected function getPollingInterval(): ?string
//     {
//         return '30s';
//     }
// }