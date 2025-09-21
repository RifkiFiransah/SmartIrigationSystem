<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class BmkgForecastChart extends ChartWidget
{
    protected static ?string $heading = 'Prakiraan Cuaca BMKG';
    protected static ?int $sort = 4;
    protected static ?string $pollingInterval = '30m';
    protected string $adm4 = '32.08.10.2001';

    public function getHeading(): string
    {
        $meta = $this->fetchForecast()['meta'] ?? [];
        $lok = $meta['desa'] ?? ($meta['kecamatan'] ?? null);
        return 'Prakiraan Cuaca BMKG'.($lok ? " - {$lok}" : '');
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $data = $this->fetchForecast();
        $entries = $data['entries'];
        if (!$entries) {
            return ['labels' => [], 'datasets' => []];
        }

        // Ambil 24 titik terdekat (24 jam-ish)
        $entries = array_slice($entries, 0, 24);

        $labels = $temps = $hums = $rains = $icons = $descs = [];
        foreach ($entries as $e) {
            $dtLocal = $e['local_datetime'] ?? $e['datetime'] ?? $e['utc_datetime'] ?? null;
            $labels[] = $this->formatLabelHour($dtLocal);
            $temps[]  = $e['t'];
            $hums[]   = $e['hu'];
            $rains[]  = $e['tp'];
            $icons[]  = $e['emoji'];
            $descs[]  = $e['weather_desc'];
        }

        return [
            'labels'       => $labels,
            'weatherIcons' => $icons,
            'weatherTexts' => $descs,
            'datasets' => [
                [
                    'label' => 'Suhu (°C)',
                    'data' => $temps,
                    'borderColor' => '#60a5fa',
                    'backgroundColor' => 'rgba(96,165,250,0.35)', // akan ditimpa gradient plugin
                    'fill' => true,
                    'tension' => 0.35,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'pointBackgroundColor' => '#3b82f6',
                    'pointBorderWidth' => 0,
                    'yAxisID' => 'yTemp',
                    'order' => 1,
                ],
                [
                    'label' => 'Kelembapan (%)',
                    'data' => $hums,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16,185,129,0.15)',
                    'pointRadius' => 0,
                    'tension' => 0.3,
                    'hidden' => true,
                    'yAxisID' => 'yHum',
                    'order' => 2,
                ],
                [
                    'type' => 'bar',
                    'label' => 'Hujan (mm)',
                    'data' => $rains,
                    'backgroundColor' => 'rgba(139,92,246,0.45)',
                    'borderColor' => 'rgba(139,92,246,0.9)',
                    'borderWidth' => 1,
                    'yAxisID' => 'yRain',
                    'barPercentage' => 0.55,
                    'categoryPercentage' => 0.7,
                    'hidden' => true,
                    'order' => 0,
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'elements' => [
                'line' => ['borderWidth' => 2],
            ],
            'layout' => [
                'padding' => ['top' => 8, 'right' => 12, 'left' => 4, 'bottom' => 4],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                    'labels' => [
                        'color' => '#cbd5e1',
                        'usePointStyle' => true,
                        'boxWidth' => 10,
                        'font' => ['size' => 11],
                    ],
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(15,23,42,0.9)',
                    'titleColor' => '#f1f5f9',
                    'bodyColor' => '#e2e8f0',
                    'borderColor' => '#334155',
                    'borderWidth' => 1,
                    'callbacks' => [
                        'title' => "function(items){ return 'Jam: '+items[0].label; }",
                        'afterBody' => "function(items){
                            const c = items[0].chart;
                            const i = items[0].dataIndex;
                            const icons = c.data.weatherIcons||[];
                            const texts = c.data.weatherTexts||[];
                            return (icons[i]||'')+' '+(texts[i]||'-');
                        }"
                    ],
                ],
                // Gradient fill + dark background
                'bgDark' => [
                    'id' => 'bgDark',
                    'beforeDraw' => "function(chart){
                        const {ctx, chartArea} = chart;
                        if(!chartArea) return;
                        const {top, bottom, left, right, width, height} = chartArea;
                        // background
                        const g = ctx.createLinearGradient(0, top, 0, bottom);
                        g.addColorStop(0,'#111827');
                        g.addColorStop(1,'#0f172a');
                        ctx.save();
                        ctx.fillStyle = g;
                        ctx.fillRect(left-8, top-8, width+16, height+16);
                        ctx.restore();
                    }"
                ],
                // Replace dataset area with gradient
                'gradientFill' => [
                    'id' => 'gradientFill',
                    'beforeDatasetsDraw' => "function(chart){
                        const ds = chart.data.datasets?.[0];
                        if(!ds) return;
                        const {ctx, chartArea:{top,bottom}} = chart;
                        const grad = ctx.createLinearGradient(0, top, 0, bottom);
                        grad.addColorStop(0,'rgba(96,165,250,0.45)');
                        grad.addColorStop(1,'rgba(96,165,250,0.02)');
                        ds.backgroundColor = grad;
                    }"
                ],
                // Emoji above temperature points
                'iconPoints' => [
                    'id' => 'iconPoints',
                    'afterDatasetsDraw' => "function(chart){
                        const icons = chart.data.weatherIcons||[];
                        const meta = chart.getDatasetMeta(0);
                        const {ctx} = chart;
                        ctx.save();
                        ctx.font='12px system-ui';
                        ctx.textAlign='center';
                        ctx.textBaseline='bottom';
                        for(let i=0;i<meta.data.length;i++){
                            const el = meta.data[i];
                            if(!el || !icons[i]) continue;
                            ctx.fillText(icons[i], el.x, el.y - 8);
                        }
                        ctx.restore();
                    }"
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => [
                        'color' => 'rgba(255,255,255,0.05)',
                        'drawBorder' => false,
                    ],
                    'ticks' => [
                        'color' => '#94a3b8',
                        'maxRotation' => 0,
                        'autoSkip' => true,
                        'font' => ['size' => 11],
                    ],
                ],
                'yTemp' => [
                    'position' => 'left',
                    'grid' => [
                        'color' => 'rgba(255,255,255,0.06)',
                        'drawBorder' => false,
                    ],
                    'ticks' => [
                        'color' => '#94a3b8',
                        'font' => ['size' => 11],
                    ],
                    'title' => [
                        'display' => true,
                        'text' => '°C',
                        'color' => '#cbd5e1',
                    ],
                ],
                'yHum' => [
                    'display' => false,
                ],
                'yRain' => [
                    'display' => false,
                ],
            ],
        ];
    }

    protected function fetchForecast(): array
    {
        return Cache::remember("bmkg_forecast_flat_minimal_{$this->adm4}", 600, function () {
            try {
                $url = "https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4={$this->adm4}";
                $res = Http::timeout(15)->acceptJson()->get($url);
                if (!$res->ok()) {
                    return ['entries' => [], 'meta' => []];
                }
                $json = $res->json();
                $metaLokasi   = $json['lokasi'] ?? [];
                $cuacaBlocks  = data_get($json, 'data.0.cuaca', []);
                $entries = [];
                foreach ($cuacaBlocks as $block) {
                    foreach (($block ?? []) as $fc) {
                        if (!is_array($fc)) continue;
                        $entries[] = [
                            'datetime'       => $fc['datetime'] ?? null,
                            'local_datetime' => $fc['local_datetime'] ?? null,
                            'utc_datetime'   => $fc['utc_datetime'] ?? null,
                            't'              => (float) ($fc['t'] ?? 0),
                            'tp'             => (float) ($fc['tp'] ?? 0),
                            'weather_desc'   => $fc['weather_desc'] ?? '',
                            'hu'             => (float) ($fc['hu'] ?? 0),
                            'emoji'          => $this->emojiFromWeather($fc['weather'] ?? null, $fc['weather_desc'] ?? ''),
                        ];
                    }
                }
                usort($entries, fn($a,$b)=>strtotime($a['local_datetime'] ?? $a['datetime'] ?? 'now')
                    <=> strtotime($b['local_datetime'] ?? $b['datetime'] ?? 'now'));
                return ['entries'=>$entries,'meta'=>$metaLokasi];
            } catch (\Throwable $e) {
                return ['entries' => [], 'meta' => []];
            }
        });
    }

    protected function formatLabelHour(?string $dt): string
    {
        if(!$dt) return '-';
        try {
            $c = Carbon::parse($dt);
            return $c->format('d H:i');
        } catch (\Throwable $e) {
            return $dt;
        }
    }

    protected function emojiFromWeather($code, string $desc): string
    {
        $d = mb_strtolower($desc);
        return match (true) {
            str_contains($d, 'petir') => '⛈️',
            str_contains($d, 'lebat') => '🌧️',
            str_contains($d, 'hujan') => '🌦️',
            str_contains($d, 'berawan') && str_contains($d,'cerah') => '⛅',
            str_contains($d, 'berawan') => '☁️',
            str_contains($d, 'kabut') => '🌫️',
            default => '☀️',
        };
    }
}