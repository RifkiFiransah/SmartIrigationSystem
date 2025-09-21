<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WeatherOverviewChart extends ChartWidget
{
    protected static ?string $heading = '🌤️ Analitik Cuaca: Angin & Cahaya (24 Jam)';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '750px';

    protected ?float $windMin = null;
    protected ?float $windMax = null;
    protected ?float $luxMin = null;
    protected ?float $luxMax = null;

    // Threshold kondisi cuaca yang lebih realistis
    protected float $brightLux = 25000;  // CERAH (>= 25K lux)
    protected float $cloudyLux = 8000;   // MENDUNG (8K-25K lux)
    protected float $dimLux = 1000;      // GELAP/MALAM (< 1K lux)

    public int $hoursWindow = 24;

    /**
     * Klasifikasi kondisi cuaca berdasarkan intensitas cahaya dan perubahan
     */
    protected function classify(?float $lux, ?float $prevLux): array
    {
        if ($lux === null) {
            return ['condition' => 'TIDAK DIKETAHUI', 'icon' => '❓', 'color' => '#9ca3af'];
        }

        // Deteksi penurunan drastis cahaya (kemungkinan hujan)
        $isRainDrop = false;
        if ($prevLux !== null && $prevLux > $this->cloudyLux) {
            $drop = ($prevLux - $lux) / $prevLux;
            if ($drop >= 0.5 && $lux < $this->cloudyLux) {
                $isRainDrop = true;
            }
        }

        // Klasifikasi berdasarkan intensitas cahaya
        if ($isRainDrop || ($lux < $this->cloudyLux && $lux >= $this->dimLux)) {
            if ($isRainDrop) {
                return ['condition' => 'HUJAN', 'icon' => '🌧️', 'color' => '#3b82f6'];
            }
            return ['condition' => 'MENDUNG', 'icon' => '☁️', 'color' => '#6b7280'];
        }
        
        if ($lux >= $this->brightLux) {
            return ['condition' => 'CERAH', 'icon' => '☀️', 'color' => '#f59e0b'];
        }
        
        if ($lux >= $this->cloudyLux) {
            return ['condition' => 'BERAWAN', 'icon' => '⛅', 'color' => '#8b5cf6'];
        }
        
        if ($lux >= $this->dimLux) {
            return ['condition' => 'MENDUNG', 'icon' => '☁️', 'color' => '#6b7280'];
        }
        
        // Malam atau sangat gelap
        return ['condition' => 'MALAM', 'icon' => '🌙', 'color' => '#374151'];
    }

    /**
     * Get weather description
     */
    protected function getWeatherDescription(string $condition): string
    {
        return match($condition) {
            'CERAH' => 'Cuaca cerah, kondisi ideal untuk aktivitas luar ruangan',
            'BERAWAN' => 'Cuaca berawan, cahaya cukup namun tidak sepenuhnya cerah',
            'MENDUNG' => 'Cuaca mendung, cahaya rendah, kemungkinan akan hujan',
            'HUJAN' => 'Kemungkinan hujan berdasarkan penurunan cahaya drastis',
            'MALAM' => 'Kondisi malam atau tempat tertutup (intensitas cahaya sangat rendah)',
            default => 'Kondisi cuaca tidak dapat ditentukan'
        };
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $window = max(1, min(24, $this->hoursWindow));
        $raw = SensorData::where('recorded_at', '>=', now()->subHours($window))
            ->orderBy('recorded_at')
            ->get();

        if ($raw->isEmpty()) {
            return [
                'datasets' => [[
                    'label' => '📊 Tidak ada data tersedia',
                    'data' => [0],
                    'backgroundColor' => '#e5e7eb',
                ]],
                'labels' => ['No Data'],
                'weatherSummary' => 'Tidak ada data cuaca tersedia'
            ];
        }

        // Group data per 2 jam
        $grouped = $raw->groupBy(function($item){
            $h = Carbon::parse($item->recorded_at)->hour;
            $interval = floor($h / 2) * 2;
            return sprintf('%02d:00', $interval);
        })->map(function($items){
            return [
                'wind' => $items->avg('wind_speed_ms'),
                'lux'  => $items->avg('light_lux'),
                'time' => $items->first()->recorded_at,
            ];
        })->values();

        $labels = [];
        $windData = [];
        $luxData = [];
        $windDelta = [];
        $luxDeltaPerc = [];
        $weatherConditions = [];
        $weatherIcons = [];
        $weatherColors = [];
        $weatherDescriptions = [];

        $prevWind = null; 
        $prevLux = null;

        foreach ($grouped as $g) {
            $time = Carbon::parse($g['time']);
            $labels[] = $time->format('H:i');
            $wind = $g['wind'] !== null ? round($g['wind'], 2) : null;
            $lux  = $g['lux'] !== null ? round($g['lux'], 0) : null;
            $windData[] = $wind;
            $luxData[] = $lux;
            $weather = $this->classify($lux, $prevLux);
            $weatherConditions[] = $weather['condition'];
            $weatherIcons[] = $weather['icon'];
            $weatherColors[] = $weather['color'];
            $weatherDescriptions[] = $this->getWeatherDescription($weather['condition']);
            if ($prevWind !== null && $wind !== null) {
                $windDelta[] = round($wind - $prevWind, 2);
            } else {
                $windDelta[] = null;
            }
            if ($prevLux !== null && $lux !== null && $prevLux > 0) {
                $luxDeltaPerc[] = round((($lux - $prevLux) / $prevLux) * 100, 1);
            } else {
                $luxDeltaPerc[] = null;
            }
            $prevWind = $wind; 
            $prevLux = $lux;
        }

        // Calculate dynamic scaling
        if (!empty($windData)) {
            $wf = array_filter($windData, fn($v)=>$v!==null);
            if (!empty($wf)) {
                $min = min($wf); $max = max($wf);
                if ($min === $max) { $min -= 0.5; $max += 0.5; }
                $range = max(0.1, $max - $min); 
                $pad = $range * 0.2;
                $this->windMin = max(0, $min - $pad);
                $this->windMax = $max + $pad;
            }
        }

        if (!empty($luxData)) {
            $lf = array_filter($luxData, fn($v)=>$v!==null);
            if (!empty($lf)) {
                $min = min($lf); $max = max($lf);
                if ($min === $max) { $min -= 100; $max += 100; }
                $range = max(100, $max - $min); 
                $pad = $range * 0.15;
                $this->luxMin = max(0, $min - $pad);
                $this->luxMax = $max + $pad;
            }
        }

        // Background colors untuk kondisi cuaca
        $conditionBgColors = array_map(function($condition) {
            return match($condition) {
                'CERAH' => 'rgba(245, 158, 11, 0.25)',      // Kuning transparan
                'BERAWAN' => 'rgba(139, 92, 246, 0.20)',    // Ungu transparan
                'MENDUNG' => 'rgba(107, 114, 128, 0.25)',   // Abu-abu transparan
                'HUJAN' => 'rgba(59, 130, 246, 0.30)',      // Biru transparan
                'MALAM' => 'rgba(55, 65, 81, 0.30)',        // Gelap transparan
                default => 'rgba(156, 163, 175, 0.15)'      // Default abu-abu
            };
        }, $weatherConditions);

        // Generate weather summary
        $conditionCounts = array_count_values($weatherConditions);
        $dominantCondition = array_key_first($conditionCounts);
        $weatherSummary = "Kondisi dominan: {$dominantCondition} ({$conditionCounts[$dominantCondition]} periode)";

        return [
            'datasets' => [
                [
                    'label' => '🌤️ Kondisi Cuaca',
                    'data' => array_fill(0, count($weatherConditions), 1),
                    'backgroundColor' => $conditionBgColors,
                    'borderWidth' => 0,
                    'yAxisID' => 'yCond',
                    'order' => 1,
                    'barPercentage' => 1.0,
                    'categoryPercentage' => 1.0,
                ],
                [
                    'label' => '💨 Kecepatan Angin (m/s)',
                    'data' => $windData,
                    'backgroundColor' => 'rgba(99, 102, 241, 0.8)',
                    'borderColor' => 'rgba(99, 102, 241, 1)',
                    'borderWidth' => 2,
                    'maxBarThickness' => 60,
                    'borderRadius' => 8,
                    'yAxisID' => 'y',
                    'order' => 3,
                    'barPercentage' => 0.6,
                    'categoryPercentage' => 0.75,
                ],
                [
                    'label' => '💡 Intensitas Cahaya (Lux)',
                    'data' => $luxData,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.6)',
                    'borderColor' => 'rgba(245, 158, 11, 1)',
                    'borderWidth' => 2,
                    'maxBarThickness' => 50,
                    'borderRadius' => 6,
                    'yAxisID' => 'y1',
                    'order' => 4,
                    'barPercentage' => 0.5,
                    'categoryPercentage' => 0.72,
                ],
                [
                    'type' => 'line',
                    'label' => '📈 Δ Angin (m/s)',
                    'data' => $windDelta,
                    'borderColor' => 'rgba(16, 185, 129, 1)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    'borderWidth' => 3,
                    'tension' => 0.4,
                    'pointRadius' => 4,
                    'pointBackgroundColor' => 'rgba(16, 185, 129, 1)',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                    'yAxisID' => 'y',
                    'order' => 5,
                ],
                [
                    'type' => 'line',
                    'label' => '📊 Δ Cahaya (%)',
                    'data' => $luxDeltaPerc,
                    'borderColor' => 'rgba(239, 68, 68, 1)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    'borderWidth' => 3,
                    'tension' => 0.4,
                    'pointRadius' => 4,
                    'pointBackgroundColor' => 'rgba(239, 68, 68, 1)',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                    'yAxisID' => 'yDelta',
                    'order' => 6,
                ],
            ],
            'labels' => $labels,
            'weatherConditions' => $weatherConditions,
            'weatherIcons' => $weatherIcons,
            'weatherColors' => $weatherColors,
            'weatherDescriptions' => $weatherDescriptions,
            'weatherSummary' => $weatherSummary,
            'luxDeltaPerc' => $luxDeltaPerc,
            'windDelta' => $windDelta,
            'thresholds' => [
                'bright' => $this->brightLux,
                'cloudy' => $this->cloudyLux,
                'dim' => $this->dimLux
            ]
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'layout' => [
                'padding' => [
                    'top' => 30,  // smaller because we use built-in legend now
                    'bottom' => 30,
                    'left' => 24,
                    'right' => 24
                ]
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'labels' => [
                        // Use pointStyle to mimic colored circles like reference
                        'usePointStyle' => true,
                        'boxWidth' => 10,
                        'font' => [ 'size' => 11 ],
                        'generateLabels' => 'function(chart){
                            const items = [];
                            // Sensor datasets (matching dataset order we want to show)
                            const dsMap = {};
                            chart.data.datasets.forEach((ds, i)=>{ dsMap[ds.label] = {text: ds.label, fillStyle: ds.backgroundColor || ds.borderColor, strokeStyle: ds.borderColor || ds.backgroundColor, hidden: chart.getDatasetMeta(i).hidden, datasetIndex: i}; });
                            const sensorOrder = ["💨 Kecepatan Angin (m/s)", "💡 Intensitas Cahaya (Lux)", "📈 Δ Angin (m/s)", "📊 Δ Cahaya (%)"]; 
                            sensorOrder.forEach(lbl=>{ if(dsMap[lbl]) { items.push(dsMap[lbl]); } });
                            // Weather condition palette (static)
                            const conditions = [
                                {text: "☀️ CERAH", color: "#f59e0b"},
                                {text: "⛅ BERAWAN", color: "#8b5cf6"},
                                {text: "☁️ MENDUNG", color: "#6b7280"},
                                {text: "🌧️ HUJAN", color: "#3b82f6"},
                                {text: "🌙 MALAM", color: "#374151"},
                            ];
                            conditions.forEach(c=> items.push({text: c.text, fillStyle: c.color, strokeStyle: c.color, hidden: false, datasetIndex: null}));
                            return items;
                        }'
                    ],
                    'onClick' => 'function(e, legendItem, legend){
                        if(legendItem.datasetIndex === null) return; // static condition item
                        const ci = legend.chart; const index = legendItem.datasetIndex; ci.toggleDataVisibility(index); ci.update();
                    }'
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(17, 24, 39, 0.95)',
                    'titleColor' => '#ffffff',
                    'titleFont' => ['size' => 14, 'weight' => 'bold'],
                    'bodyColor' => '#ffffff',
                    'bodyFont' => ['size' => 12],
                    'footerColor' => '#d1d5db',
                    'footerFont' => ['size' => 11, 'style' => 'italic'],
                    'borderColor' => 'rgba(75, 85, 99, 0.5)',
                    'borderWidth' => 1,
                    'cornerRadius' => 12,
                    'displayColors' => true,
                    'padding' => 15,
                    'caretPadding' => 10,
                    'mode' => 'index',
                    'intersect' => false,
                    'callbacks' => [
                        'title' => 'function(context) {
                            return "🕐 Waktu: " + context[0].label;
                        }',
                        'beforeBody' => 'function(items) {
                            if (!items.length) return "";
                            const idx = items[0].dataIndex;
                            const chart = items[0].chart;
                            const condition = chart.data.weatherConditions ? chart.data.weatherConditions[idx] : null;
                            const icon = chart.data.weatherIcons ? chart.data.weatherIcons[idx] : "";
                            const desc = chart.data.weatherDescriptions ? chart.data.weatherDescriptions[idx] : "";
                            if (condition) {
                                return [
                                    "━━━━━━━━━━━━━━━━━━━━━━━━",
                                    icon + " CUACA: " + condition,
                                    "ℹ️ " + desc,
                                    "━━━━━━━━━━━━━━━━━━━━━━━━"
                                ];
                            }
                            return "";
                        }'
                    ]
                ],
                'weatherLabels' => [
                    'id' => 'weatherLabels',
                    'afterDatasetsDraw' => 'function(chart) {
                        const ctx = chart.ctx;
                        const chartArea = chart.chartArea;
                        const conditions = chart.data.weatherConditions || [];
                        const icons = chart.data.weatherIcons || [];
                        const meta = chart.getDatasetMeta(0);
                        
                        if (chart.width < 600) return; // Hide on small screens
                        
                        ctx.save();
                        ctx.font = "bold 16px Inter, system-ui, sans-serif";
                        ctx.textAlign = "center";
                        ctx.textBaseline = "bottom";
                        
                        conditions.forEach((condition, i) => {
                            if (!condition || condition === "TIDAK DIKETAHUI") return;
                            
                            const element = meta.data[i];
                            if (!element) return;
                            
                            const x = element.x;
                            const icon = icons[i] || "";
                            
                            // Draw icon
                            ctx.fillText(icon, x, chartArea.top - 5);
                            
                            // Draw condition text
                            ctx.font = "bold 9px Inter, system-ui, sans-serif";
                            ctx.fillStyle = "#374151";
                            ctx.fillText(condition, x, chartArea.top + 15);
                        });
                        
                        ctx.restore();
                    }'
                ]
            ],
            'scales' => [
                'yCond' => [
                    'display' => false,
                    'min' => 0,
                    'max' => 1
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => '🕐 Waktu (' . $this->hoursWindow . ' jam terakhir)',
                        'font' => ['size' => 12, 'weight' => 'bold'],
                        'color' => '#374151'
                    ],
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(156, 163, 175, 0.2)'
                    ],
                    'ticks' => [
                        'color' => '#6b7280',
                        'font' => [
                            'size' => 11,
                            'family' => 'Inter, system-ui, sans-serif'
                        ],
                    ],
                    'border' => ['display' => false],
                ],
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'beginAtZero' => true,
                    'min' => $this->windMin,
                    'max' => $this->windMax,
                    'title' => [
                        'display' => true,
                        'text' => '💨 Angin (m/s)',
                        'font' => ['size' => 12, 'weight' => 'bold'],
                        'color' => '#6366f1'
                    ],
                    'grid' => [
                        'color' => 'rgba(99, 102, 241, 0.1)',
                        'lineWidth' => 1
                    ],
                    'ticks' => [
                        'color' => '#6366f1',
                        'font' => ['size' => 10]
                    ],
                    'border' => ['display' => false],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'beginAtZero' => true,
                    'min' => $this->luxMin,
                    'max' => $this->luxMax,
                    'title' => [
                        'display' => true,
                        'text' => '💡 Cahaya (Lux)',
                        'font' => ['size' => 12, 'weight' => 'bold'],
                        'color' => '#f59e0b'
                    ],
                    'grid' => [
                        'drawOnChartArea' => false
                    ],
                    'ticks' => [
                        'color' => '#f59e0b',
                        'font' => ['size' => 10]
                    ],
                    'border' => ['display' => false],
                ],
                'yDelta' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Δ Perubahan',
                        'font' => ['size' => 12, 'weight' => 'bold'],
                        'color' => '#10b981'
                    ],
                    'grid' => [
                        'color' => 'rgba(16, 185, 129, 0.1)'
                    ],
                    'ticks' => [
                        'color' => '#10b981',
                        'font' => ['size' => 9]
                    ],
                    'border' => ['display' => false],
                    'suggestedMin' => -100,
                    'suggestedMax' => 100,
                ],
            ],
            'elements' => [
                'bar' => [
                    'borderRadius' => 4,
                    'borderSkipped' => false
                ],
                'point' => [
                    'hoverBorderWidth' => 3,
                    'hoverRadius' => 6
                ]
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index'
            ],
            'animation' => [
                'duration' => 1000,
                'easing' => 'easeInOutCubic'
            ]
        ];
    }
}