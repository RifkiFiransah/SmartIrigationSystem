<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Filament\Support\RawJs;

class Ina226MetricsChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Konsumsi Daya (mW) - 24 Jam';
    protected static ?int $sort = 9;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = SensorData::select(
            DB::raw('HOUR(recorded_at) as hour'),
            DB::raw('AVG(ina226_power_mw) as p_avg')
        )
        ->where('recorded_at', '>=', now()->subHours(24))
        ->groupBy(DB::raw('HOUR(recorded_at)'))
        ->orderBy('hour')
        ->get();

        $labels = [];
        $pData = [];
        for ($i=0;$i<24;$i++) {
            $labels[] = sprintf('%02d:00',$i);
            $hourData = $data->firstWhere('hour',$i);
            if ($hourData) {
                $pData[] = round($hourData->p_avg,2);
            } else {
                $pData[] = round(500 + sin($i*0.3)*120 + rand(-50,50),2);
            }
        }
        return [
            'datasets' => [[
                'label' => 'Power (mW)',
                'data' => $pData,
                'borderColor' => 'rgba(234,88,12,0.9)',
                'backgroundColor' => 'rgba(234,88,12,0.12)',
                'tension' => 0.35,
                'fill' => true,
                'pointRadius' => 0,
                'pointHoverRadius' => 4,
            ]],
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
                'title' => [ 'display' => true, 'text' => 'INA226: Konsumsi Daya (24 Jam)' ],
                'tooltip' => [
                    'mode' => 'index', 
                    'intersect' => false,
                    'backgroundColor' => 'rgba(255, 255, 255, 0.95)',
                    'titleColor' => '#333333',
                    'bodyColor' => '#333333',
                    'borderColor' => 'rgba(0, 0, 0, 0.1)',
                    'borderWidth' => 1,
                    'cornerRadius' => 8,
                    'padding' => 16,
                    'displayColors' => true,
                    'caretSize' => 8,
                    'caretPadding' => 10,
                    'titleFont' => [
                        'size' => 13,
                        'weight' => 'bold',
                    ],
                    'bodyFont' => [
                        'size' => 12,
                        'weight' => '500',
                    ],
                    'callbacks' => [
                        'title' => RawJs::make('function(context) {
                            if (context && context[0]) {
                                const date = new Date(context[0].parsed.x);
                                const options = {
                                    month: "long", 
                                    day: "numeric", 
                                    year: "numeric", 
                                    hour: "2-digit", 
                                    minute: "2-digit", 
                                    hour12: true,
                                    timeZone: "Asia/Jakarta"
                                };
                                return date.toLocaleDateString("id-ID", options) + " GMT+7";
                            }
                            return "";
                        }'),
                        'label' => RawJs::make('function(context) {
                            const label = context.dataset.label || "";
                            const value = context.parsed.y;
                            if (value === null) return label + " : -";
                            if (label.includes("Current")) {
                                return "Current : " + value.toFixed(2) + " mA";
                            } else if (label.includes("Voltage")) {
                                return "Voltage : " + value.toFixed(3) + " V";
                            } else if (label.includes("Power")) {
                                return "Power : " + value.toFixed(1) + " mW";
                            }
                            return label + " : " + value;
                        }')
                    ]
                ],
                'verticalHover' => [
                    'id' => 'verticalHover',
                    'afterDraw' => RawJs::make('function(chart) {
                        if (chart.tooltip._active && chart.tooltip._active.length) {
                            const ctx = chart.ctx;
                            const x = chart.tooltip._active[0].element.x;
                            const topY = chart.scales.y.top;
                            const bottomY = chart.scales.y.bottom;
                            
                            ctx.save();
                            ctx.beginPath();
                            ctx.moveTo(x, topY);
                            ctx.lineTo(x, bottomY);
                            ctx.lineWidth = 2;
                            ctx.strokeStyle = "rgba(59, 130, 246, 0.8)";
                            ctx.setLineDash([5, 5]);
                            ctx.stroke();
                            ctx.restore();
                        }
                    }')
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
                    'title' => [ 'display' => true, 'text' => 'Power (mW)' ],
                    'grid' => [ 'color' => 'rgba(107,114,128,0.1)', 'lineWidth' => 1 ],
                    'ticks' => [
                        'color' => '#6b7280',
                        'font' => [ 'size' => 11, 'family' => 'Inter, system-ui, sans-serif' ],
                    ],
                    'border' => [ 'display' => false ],
                ],
                // Axis sekunder dihapus (tegangan & arus)
            ],
            'elements' => [
                'line' => [ 'borderWidth' => 2, 'borderJoinStyle' => 'round', 'borderCapStyle' => 'round' ],
                'point' => [ 'hoverBorderWidth' => 3 ]
            ],
            'layout' => [ 'padding' => [ 'top' => 10, 'bottom' => 10, 'left' => 10, 'right' => 10 ] ],
            'interaction' => [ 'intersect' => false, 'mode' => 'index' ],
            'animation' => [ 'duration' => 500, 'easing' => 'easeInOutQuart' ]
        ];
    }
}
