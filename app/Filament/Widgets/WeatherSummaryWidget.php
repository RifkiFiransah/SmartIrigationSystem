<?php

namespace App\Filament\Widgets;

use App\Services\BMKGWeatherService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class WeatherSummaryWidget extends Widget
{
    protected static string $view = 'filament.widgets.weather-summary-widget';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $pollingInterval = '5m';

    public ?array $weather = null;
    public ?array $forecast = null; // flat entries (subset for chart)
    protected string $adm4 = '32.08.10.2001';

    public function mount(BMKGWeatherService $service): void
    { $this->loadWeather($service); $this->loadForecast(); }

    public function refreshWeather(BMKGWeatherService $service): void
    { $this->loadWeather($service, true); $this->loadForecast(true); }

    private function loadWeather(BMKGWeatherService $service, bool $force=false): void
    { $this->weather = $service->getWeather(-6.2, 106.8166); }

    private function loadForecast(bool $force=false): void
    {
        $cacheKey = "bmkg_forecast_inline_{$this->adm4}";
        $data = Cache::remember($cacheKey, 600, function(){ return $this->fetchForecastRaw(); });
        $this->forecast = $this->transformForecast($data);
    }

    private function fetchForecastRaw(): array
    {
        try {
            $url = "https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4={$this->adm4}";
            $res = Http::timeout(15)->acceptJson()->get($url);
            if(!$res->ok()) return [];
            return $res->json();
        } catch(\Throwable $e) { return []; }
    }

    private function transformForecast(array $json): array
    {
        $cuacaBlocks  = data_get($json, 'data.0.cuaca', []);
        $entries = [];
        foreach ($cuacaBlocks as $block) {
            foreach (($block ?? []) as $fc) {
                if (!is_array($fc)) continue;
                $entries[] = [
                    'datetime'       => $fc['local_datetime'] ?? $fc['datetime'] ?? null,
                    't'              => (float) ($fc['t'] ?? 0),
                    'tp'             => (float) ($fc['tp'] ?? 0),
                    'hu'             => (float) ($fc['hu'] ?? 0),
                    'desc'           => $fc['weather_desc'] ?? '',
                    'emoji'          => $this->emojiFromWeather($fc['weather'] ?? null, $fc['weather_desc'] ?? ''),
                ];
            }
        }
        usort($entries, fn($a,$b)=>strtotime($a['datetime']??'now')<=>strtotime($b['datetime']??'now'));
        $entries = array_slice($entries,0,24);
        // Build mini-series arrays for blade
        $labels = $temps = [];
        foreach ($entries as $e) { $labels[] = Carbon::parse($e['datetime'])->format('H:i'); $temps[]=$e['t']; }
        return [ 'entries'=>$entries, 'labels'=>$labels, 'temps'=>$temps ];
    }

    public function getConditionIcon(): string
    { $condition = $this->weather['condition'] ?? 'cerah'; return match($condition){ 'hujan'=>'heroicon-m-cloud', 'mendung'=>'heroicon-m-cloud', default=>'heroicon-m-sun' }; }

    private function emojiFromWeather($code, string $desc): string
    { $d = mb_strtolower($desc); return match(true){ str_contains($d,'petir')=>'⛈️', str_contains($d,'lebat')=>'🌧️', str_contains($d,'hujan')=>'🌦️', str_contains($d,'berawan')&&str_contains($d,'cerah')=>'⛅', str_contains($d,'berawan')=>'☁️', str_contains($d,'kabut')=>'🌫️', default=>'☀️'}; }
}
