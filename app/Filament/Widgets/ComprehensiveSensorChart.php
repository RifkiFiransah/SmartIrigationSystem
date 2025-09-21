<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ComprehensiveSensorChart extends ChartWidget
{
    protected static ?string $heading = 'Perbandingan Data Sensor (Bar) - 24 Jam Terakhir';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '460px';
    // Toggle tampilkan zona ideal (50-100) di axis kiri
    public bool $showBand = true;
    // Window jam terakhir yang ditampilkan (default 6 jam). Gunakan 24 untuk penuh.
    public int $hoursWindow = 24; // nanti bisa diubah dari luar (misal melalui form)
    // Jika true, pakai range tetap 1 - 100 untuk axis kiri agar detail per perubahan kecil lebih konsisten.
    public bool $fixedRange = false;
    // Catatan: scaleConfig dipakai sebelumnya untuk auto-scale dinamis.
    // Jika ingin mengaktifkan kembali (misal untuk line chart), hapus komentar dan gunakan kembali di getOptions.
    protected array $scaleConfig = [];

    protected function getData(): array
    {
        $data = SensorData::select(
            DB::raw('HOUR(recorded_at) as hour'),
            DB::raw('AVG(temperature_c) as avg_temp'),
            DB::raw('AVG(soil_moisture_pct) as avg_soil'),
            DB::raw('AVG(water_volume_l) as avg_volume'),
            DB::raw('AVG(light_lux) as avg_light'),
            DB::raw('AVG(wind_speed_ms) as avg_wind'),
            DB::raw('AVG(water_height_cm) as avg_height'),
            DB::raw('AVG(ina226_power_mw) as avg_power')
        )
        ->where('recorded_at', '>=', now()->subHours(24))
        ->groupBy(DB::raw('HOUR(recorded_at)'))
        ->orderBy('hour')
        ->get();

        $labels = [];
        $tempData = [];
        $soilData = [];
        $volumeData = [];
        $lightData = [];
        $windData = [];
        $heightData = [];
        $powerData = [];

        $window = max(1, min(24, $this->hoursWindow));
        $startHour = now()->copy()->subHours($window)->hour; // hanya untuk referensi label; kita tetap pakai jam 0-23 hari berjalan
        // Step adaptif: jika window <= 12 tampilkan tiap jam, kalau lebih pakai kelipatan 2
        $step = $window <= 12 ? 1 : 2;
        for ($i = 0; $i < $window; $i += $step) {
            // ambil jam absolut relatif ke sekarang mundur window
            $hourLabel = now()->copy()->subHours($window - 1 - $i)->format('H:00');
            $labels[] = $hourLabel;
            $targetHour = (int) now()->copy()->subHours($window - 1 - $i)->format('G');
            $hourData = $data->firstWhere('hour', $targetHour);
            
            if ($hourData) {
                $tempData[] = round($hourData->avg_temp, 1);
                $soilData[] = round($hourData->avg_soil, 1);
                $volumeData[] = round($hourData->avg_volume, 2);
                $lightData[] = round($hourData->avg_light / 1000, 1); // Convert to K-Lux
                $windData[] = round($hourData->avg_wind, 2);
                $heightData[] = round($hourData->avg_height, 1);
                $powerData[] = round($hourData->avg_power / 100, 2); // Convert to 100mW units
            } else {
                $tempData[] = null;
                $soilData[] = null;
                $volumeData[] = null;
                $lightData[] = null;
                $windData[] = null;
                $heightData[] = null;
                $powerData[] = null;
            }
        }

        // Utility: Hitung min/max dengan padding
        $calcRange = function(array $values, float $paddingPercent = 10) {
            $filtered = array_values(array_filter($values, fn($v) => $v !== null));
            if (empty($filtered)) {
                return [null, null];
            }
            $min = min($filtered);
            $max = max($filtered);
            if ($min === $max) {
                // Jika semua nilai sama, beri rentang kecil di sekitar nilai itu
                $delta = ($min == 0 ? 1 : abs($min) * 0.1);
                return [round($min - $delta, 2), round($max + $delta, 2)];
            }
            $range = $max - $min;
            $pad = $range * ($paddingPercent / 100);
            return [round($min - $pad, 2), round($max + $pad, 2)];
        };

        // (Opsional) Amplifikasi range kecil agar tidak terlihat terlalu datar.
        // Aktifkan dengan mengganti 'false' menjadi 'true'.
        $amplifySmallRange = false;
        if ($amplifySmallRange) {
            $amplify = function(array $range, float $minSpan = 5.0) {
                [$min, $max] = $range; if ($min === null || $max === null) return $range; 
                if (($max - $min) < $minSpan) {
                    $mid = ($min + $max)/2; $half = $minSpan/2; return [round($mid - $half,2), round($mid + $half,2)];
                }
                return $range;
            };
        }
        // Range axis kiri (gabung variabel sejenis skala rendah-menengah)
        [$yMin, $yMax] = $calcRange(array_merge($tempData, $soilData, $windData, $heightData));
        // Range axis kanan (variabel skala tinggi / berbeda satuan besar)
        [$y1Min, $y1Max] = $calcRange(array_merge($volumeData, $lightData, $powerData));

        $this->scaleConfig = [
            'y' => ['min' => $yMin, 'max' => $yMax],
            'y1' => ['min' => $y1Min, 'max' => $y1Max],
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Suhu (°C)',
                    'data' => $tempData,
                    'backgroundColor' => 'rgba(239,68,68,0.75)',
                    'borderColor' => 'rgba(239,68,68,1)',
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                    'maxBarThickness' => 56,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Kelembapan Tanah (%)',
                    'data' => $soilData,
                    'backgroundColor' => 'rgba(34,197,94,0.75)',
                    'borderColor' => 'rgba(34,197,94,1)',
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                    'maxBarThickness' => 56,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Volume Air (L)',
                    'data' => $volumeData,
                    'backgroundColor' => 'rgba(59,130,246,0.75)',
                    'borderColor' => 'rgba(59,130,246,1)',
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                    'maxBarThickness' => 56,
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'Cahaya (K-Lux)',
                    'data' => $lightData,
                    'backgroundColor' => 'rgba(245,158,11,0.75)',
                    'borderColor' => 'rgba(245,158,11,1)',
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                    'maxBarThickness' => 56,
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'Angin (m/s)',
                    'data' => $windData,
                    'backgroundColor' => 'rgba(99,102,241,0.75)',
                    'borderColor' => 'rgba(99,102,241,1)',
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                    'maxBarThickness' => 56,
                    'yAxisID' => 'y',
                    'hidden' => true,
                ],
                [
                    'label' => 'Tinggi Air (cm)',
                    'data' => $heightData,
                    'backgroundColor' => 'rgba(14,165,233,0.75)',
                    'borderColor' => 'rgba(14,165,233,1)',
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                    'maxBarThickness' => 56,
                    'yAxisID' => 'y',
                    'hidden' => true,
                ],
                [
                    'label' => 'Daya (100mW)',
                    'data' => $powerData,
                    'backgroundColor' => 'rgba(234,88,12,0.75)',
                    'borderColor' => 'rgba(234,88,12,1)',
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                    'maxBarThickness' => 56,
                    'yAxisID' => 'y1',
                    'hidden' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        $y = $this->scaleConfig['y'] ?? ['min' => null, 'max' => null];
        // Jika fixedRange aktif, pakai 1-100 kecuali data real melebihi 100
        $fixedMin = 1;
        $fixedMax = 100;
        if ($this->fixedRange) {
            $observedMax = $y['max'] ?? null;
            if ($observedMax !== null && $observedMax > $fixedMax) {
                // naikkan ke kelipatan 10 di atas observedMax
                $fixedMax = (int) (ceil($observedMax / 10) * 10);
            }
        }
        $y1 = $this->scaleConfig['y1'] ?? ['min' => null, 'max' => null];

        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                        'font' => ['size' => 11, 'weight' => '600'],
                        'padding' => 12,
                        'boxWidth' => 12,
                        'boxHeight' => 12,
                    ]
                ],
                    // Plugin highlight band hanya dimunculkan jika $showBand = true
                    ...( $this->showBand ? [ 'highlightBand' => [
                        'id' => 'highlightBand',
                        'beforeDraw' => 'function(chart, args, opts) {\n const yScale = chart.scales.y; if (!yScale) return;\n const ctx = chart.ctx; const top = yScale.getPixelForValue(100); const bottom = yScale.getPixelForValue(50);\n ctx.save();\n ctx.fillStyle = \"rgba(16,185,129,0.12)\";\n ctx.fillRect(chart.chartArea.left, top, chart.chartArea.right - chart.chartArea.left, bottom - top);\n ctx.strokeStyle = \"rgba(16,185,129,0.55)\"; ctx.lineWidth = 1.5;\n ctx.beginPath(); ctx.moveTo(chart.chartArea.left, top); ctx.lineTo(chart.chartArea.right, top); ctx.stroke();\n ctx.beginPath(); ctx.moveTo(chart.chartArea.left, bottom); ctx.lineTo(chart.chartArea.right, bottom); ctx.stroke();\n ctx.restore();\n }'
                    ] ] : [] ),
                'title' => [
                    'display' => true,
                    'text' => 'Grafik Monitoring Sensor IoT - Tren 24 Jam Terakhir (Auto-Scaled)',
                    'font' => ['size' => 16, 'weight' => 'bold'],
                    'color' => '#374151',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'titleColor' => '#fff',
                    'bodyColor' => '#fff',
                    'borderColor' => 'rgba(255, 255, 255, 0.2)',
                    'borderWidth' => 1,
                    'cornerRadius' => 8,
                    'displayColors' => true,
                    'callbacks' => [
                        'title' => 'function(items) { if(!items.length) return ""; return items[0].label + ""; }',
                        'label' => 'function(context) {
                            const label = context.dataset.label || "";
                            const v = context.parsed.y;
                            if (v === null || v === undefined) return label + ": -";
                            const num = (Math.abs(v) < 10 ? v.toFixed(2) : v.toFixed(1));
                            return label + ": " + num;
                        }'
                    ]
                ],
                // Plugin inline sederhana untuk highlight dataset saat hover
                'customPlugin' => [
                    'id' => 'highlightOnHover',
                    'afterEvent' => 'function(chart, evt) {
                        const active = chart.getActiveElements();
                        const datasets = chart.data.datasets;
                        if (active.length) {
                            const datasetIndex = active[0].datasetIndex;
                            datasets.forEach((ds, i) => {
                                const meta = chart.getDatasetMeta(i);
                                meta.hidden = meta.hidden === null ? null : meta.hidden; // keep state
                                if (i !== datasetIndex) {
                                    meta.$opacity = 0.25;
                                } else {
                                    meta.$opacity = 1;
                                }
                            });
                        } else {
                            datasets.forEach((ds, i) => {
                                const meta = chart.getDatasetMeta(i);
                                meta.$opacity = 1;
                            });
                        }
                        chart.draw();
                    }',
                    'beforeDatasetDraw' => 'function(chart, args, pluginOptions) {
                        const meta = chart.getDatasetMeta(args.index);
                        const opacity = meta.$opacity !== undefined ? meta.$opacity : 1;
                        const ctx = chart.ctx;
                        ctx.save();
                        ctx.globalAlpha = opacity;
                    }',
                    'afterDatasetDraw' => 'function(chart, args, pluginOptions) { chart.ctx.restore(); }'
                ],
            ],
            'scales' => [
                'x' => [
                    'title' => [
                        'display' => true, 
                        'text' => 'Waktu (Jam)',
                        'font' => ['size' => 12, 'weight' => 'bold']
                    ],
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(0,0,0,0.04)',
                        'lineWidth' => 1,
                    ],
                    'ticks' => [
                        'font' => ['size' => 11],
                        'color' => '#6B7280',
                        'autoSkip' => false,
                    ]
                ],
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true, 
                        'text' => 'Suhu (°C) / Kelembapan (%) / Angin (m/s) / Tinggi Air (cm)',
                        'font' => ['size' => 10, 'weight' => 'bold'],
                        'color' => '#374151'
                    ],
                    'beginAtZero' => !$this->fixedRange,
                    'min' => $this->fixedRange ? $fixedMin : ($y['min'] ?? 0),
                    'max' => $this->fixedRange ? $fixedMax : null,
                    'suggestedMax' => $this->fixedRange ? null : max(($y['max'] ?? 100), 100),
                    // 'max' bisa dibiarkan auto; jika mau paksa gunakan 'suggestedMax'
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(0,0,0,0.12)',
                        'lineWidth' => 1,
                    ],
                    'ticks' => [
                        'font' => ['size' => 10],
                        'color' => '#6B7280',
                        'stepSize' => $this->fixedRange ? 10 : null,
                    ]
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true, 
                        'text' => 'Volume Air (L) / Cahaya (K-Lux) / Daya (×100mW)',
                        'font' => ['size' => 10, 'weight' => 'bold'],
                        'color' => '#374151'
                    ],
                    'beginAtZero' => true,
                    'min' => $y1['min'] ?? 0,
                    'grid' => [
                        'drawOnChartArea' => false,
                        'color' => 'rgba(0,0,0,0.05)',
                    ],
                    'ticks' => [
                        'font' => ['size' => 10],
                        'color' => '#6B7280',
                    ]
                ],
            ],
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'animation' => [
                'duration' => 1000,
                'easing' => 'easeInOutQuart',
            ],
            'hover' => [
                'mode' => 'index',
                'intersect' => false,
                'animationDuration' => 200,
            ],
            'elements' => [
                'line' => [
                    'borderJoinStyle' => 'round',
                    'borderCapStyle' => 'round',
                ],
                'point' => [
                    'hoverBorderWidth' => 3,
                ]
            ],
        ];
    }
}
