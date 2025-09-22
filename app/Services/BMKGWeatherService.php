<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

class BMKGWeatherService
{
    private string $provider;

    public function __construct()
    {
        $this->provider = config('weather.provider', 'open-meteo');
    }
    /**
     * Ambil data cuaca eksternal (BMKG / proxy). Untuk demo kita gunakan endpoint publik sederhana
     * atau fallback synthetic bila request gagal.
     *
     * @param float $lat
     * @param float $lon
     * @return array{source:string,updated_at:string,temperature:float|null,humidity:int|null,wind_speed:float|null,light_lux:int|null,condition:string,raw:array}
     */
    public function getWeather(float $lat, float $lon): array
    {
        $cacheKey = sprintf('weather:current:%s:%.2f:%.2f', $this->provider, $lat, $lon);
        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($lat, $lon) {
            // Provider priority: configured provider -> open-meteo -> synthetic
            $result = null;
            if ($this->provider === 'bmkg') {
                $result = $this->fetchBmkgCurrent();
            }
            if (!$result && $this->provider !== 'open-meteo') {
                $result = $this->fetchOpenMeteoCurrent($lat, $lon);
            }
            if (!$result && $this->provider === 'open-meteo') {
                $result = $this->fetchOpenMeteoCurrent($lat, $lon);
            }
            if (!$result) {
                $result = $this->syntheticFallback();
            }
            return $result;
        });
    }

    /**
     * Ambil deret waktu (time-series) eksternal hingga N jam (default 24) untuk grafik.
     * Return shape:
     * [
     *   'source' => string,
     *   'updated_at' => iso8601,
     *   'hours' => [
     *       ['time' => '2025-09-22T10:00:00+07:00','temperature'=>..,'humidity'=>..,'wind_speed'=>..,'light_lux'=>..,'condition'=>'cerah'],
     *       ...
     *   ]
     * ]
     */
    public function getHourly(float $lat, float $lon, int $hours = 24): array
    {
        $hours = max(1, min(72, $hours));
        $cacheKey = sprintf('weather:hourly:%s:%.2f:%.2f:%d', $this->provider, $lat, $lon, $hours);
        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($lat, $lon, $hours) {
            $series = null;
            $source = null;
            if ($this->provider === 'bmkg') {
                $parsed = $this->fetchBmkgHourly($hours);
                if ($parsed) { $series = $parsed['series']; $source = 'bmkg'; }
            }
            if (!$series) {
                $open = $this->fetchOpenMeteoHourly($lat, $lon, $hours);
                if ($open) { $series = $open; $source = 'open-meteo'; }
            }
            if (!$series) {
                $series = $this->syntheticHourly($hours);
                $source = 'synthetic';
            }
            return [
                'source' => $source,
                'updated_at' => now()->toIso8601String(),
                'hours' => $series,
            ];
        });
    }

    private function classifyCondition(?float $temp, ?int $humidity, ?float $wind): string
    {
        if ($humidity !== null && $humidity > 85 && $wind !== null && $wind > 5) return 'hujan';
        if ($humidity !== null && $humidity > 70) return 'mendung';
        return 'cerah';
    }

    private function estimateLux(string $condition): int
    {
        return match($condition) {
            'hujan' => rand(800, 2500),
            'mendung' => rand(2500, 12000),
            default => rand(15000, 55000),
        };
    }

    private function syntheticFallback(): array
    {
        $now = now();
        $hour = (int)$now->format('G');
        $temp = 24 + sin($hour/24 * 2 * M_PI) * 6 + rand(-1,2);
        $humidity = 60 + rand(-5,15);
        $wind = max(0, 2 + cos($hour/24 * 2 * M_PI) * 2 + rand(-1,2)/2);
        $condition = $this->classifyCondition($temp, $humidity, $wind);
        return [
            'source' => 'synthetic',
            'updated_at' => $now->toIso8601String(),
            'temperature' => round($temp,1),
            'humidity' => (int)$humidity,
            'wind_speed' => round($wind,2),
            'light_lux' => $this->estimateLux($condition),
            'condition' => $condition,
            'raw' => [],
        ];
    }

    private function fetchOpenMeteoCurrent(float $lat, float $lon): ?array
    {
        try {
            $resp = Http::timeout(5)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $lat,
                'longitude' => $lon,
                'hourly' => 'temperature_2m,relativehumidity_2m,windspeed_10m',
                'current_weather' => true,
                'timezone' => 'Asia/Jakarta'
            ]);
            if ($resp->ok()) {
                $json = $resp->json();
                $cw = $json['current_weather'] ?? [];
                $temp = $cw['temperature'] ?? null;
                $wind = $cw['windspeed'] ?? null;
                $humidity = null;
                if (isset($json['hourly']['time'])) {
                    $idx = array_search($cw['time'] ?? null, $json['hourly']['time']);
                    if ($idx !== false && isset($json['hourly']['relativehumidity_2m'][$idx])) {
                        $humidity = (int) $json['hourly']['relativehumidity_2m'][$idx];
                    }
                }
                $condition = $this->classifyCondition($temp, $humidity, $wind);
                return [
                    'source' => 'open-meteo',
                    'updated_at' => now()->toIso8601String(),
                    'temperature' => $temp !== null ? (float)$temp : null,
                    'humidity' => $humidity,
                    'wind_speed' => $wind !== null ? (float)$wind : null,
                    'light_lux' => $this->estimateLux($condition),
                    'condition' => $condition,
                    'raw' => $json,
                ];
            }
        } catch (\Throwable $e) {}
        return null;
    }

    private function fetchOpenMeteoHourly(float $lat, float $lon, int $hours): ?array
    {
        try {
            $resp = Http::timeout(6)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $lat,
                'longitude' => $lon,
                'hourly' => 'temperature_2m,relativehumidity_2m,windspeed_10m',
                'timezone' => 'Asia/Jakarta'
            ]);
            if ($resp->ok()) {
                $json = $resp->json();
                $times = $json['hourly']['time'] ?? [];
                $temps = $json['hourly']['temperature_2m'] ?? [];
                $hums = $json['hourly']['relativehumidity_2m'] ?? [];
                $winds = $json['hourly']['windspeed_10m'] ?? [];
                $sliceCount = min($hours, count($times));
                $offset = max(0, count($times) - $sliceCount);
                $series = [];
                for ($i = $offset; $i < $offset + $sliceCount; $i++) {
                    $temp = isset($temps[$i]) ? (float)$temps[$i] : null;
                    $hum = isset($hums[$i]) ? (int)$hums[$i] : null;
                    $wind = isset($winds[$i]) ? (float)$winds[$i] : null;
                    $cond = $this->classifyCondition($temp, $hum, $wind);
                    $series[] = [
                        'time' => Carbon::parse($times[$i], 'Asia/Jakarta')->toIso8601String(),
                        'temperature' => $temp,
                        'humidity' => $hum,
                        'wind_speed' => $wind,
                        'light_lux' => $this->estimateLux($cond),
                        'condition' => $cond,
                    ];
                }
                return $series;
            }
        } catch (\Throwable $e) {}
        return null;
    }

    private function syntheticHourly(int $hours): array
    {
        $series = [];
        for ($i = $hours - 1; $i >= 0; $i--) {
            $t = now()->copy()->subHours($i);
            $hour = (int)$t->format('G');
            $temp = 24 + sin($hour/24 * 2 * M_PI) * 6 + rand(-1,2);
            $hum = 60 + rand(-5,15);
            $wind = max(0, 2 + cos($hour/24 * 2 * M_PI) * 2 + rand(-1,2)/2);
            $cond = $this->classifyCondition($temp, $hum, $wind);
            $series[] = [
                'time' => $t->toIso8601String(),
                'temperature' => round($temp,1),
                'humidity' => (int)$hum,
                'wind_speed' => round($wind,2),
                'light_lux' => $this->estimateLux($cond),
                'condition' => $cond,
            ];
        }
        return $series;
    }

    private function fetchBmkgCurrent(): ?array
    {
        $data = $this->fetchBmkgRaw();
        if (!$data) return null;
        // BMKG structure: we search first area parameter for current hour
        $series = $this->parseBmkgToSeries($data, 1); // hours ignored, just ensure at least 1 entry
        if (!$series) return null;
        $current = end($series); // last is current
        return [
            'source' => 'bmkg',
            'updated_at' => now()->toIso8601String(),
            'temperature' => $current['temperature'] ?? null,
            'humidity' => $current['humidity'] ?? null,
            'wind_speed' => $current['wind_speed'] ?? null,
            'light_lux' => $current['light_lux'] ?? null,
            'condition' => $current['condition'] ?? 'cerah',
            'raw' => $data,
        ];
    }

    private function fetchBmkgHourly(int $hours): ?array
    {
        $data = $this->fetchBmkgRaw();
        if (!$data) return null;
        $parsed = $this->parseBmkgToSeries($data, $hours);
        return $parsed ? ['series' => $parsed] : null;
    }

    private function fetchBmkgRaw(): ?array
    {
        $url = config('weather.bmkg.forecast_url');
        if (!$url) return null;
        try {
            $resp = Http::timeout(8)->get($url);
            if ($resp->ok()) {
                return $resp->json();
            }
        } catch (\Throwable $e) {}
        return null;
    }

    private function parseBmkgToSeries(array $data, int $hours): array
    {
        // BMKG new public API shape (example) assumed: data contains array 'data'->'areas' each with 'parameters'
        // We will attempt to locate parameters by id/name: t (temperature), hu (humidity), ws (wind speed) etc.
        // Actual shape may differ; implement defensive parsing.
        $areas = $data['data']['areas'] ?? $data['areas'] ?? [];
        if (empty($areas)) return [];
        $area = $areas[0];
        $params = $area['parameters'] ?? [];
        $map = [];
        foreach ($params as $p) {
            $id = $p['id'] ?? ($p['name'] ?? null);
            if ($id) $map[$id] = $p['times'] ?? [];
        }
        $tempTimes = $map['t'] ?? [];
        $humTimes = $map['hu'] ?? [];
        $windTimes = $map['ws'] ?? []; // may be m/s already
        // Condition sometimes parameter 'weather' or 'weather_desc' (guess id 'weather')
        $condTimes = $map['weather'] ?? ($map['weather_desc'] ?? []);
        $series = [];
        // Align by index (BMKG arrays usually same length/time points). We'll take last $hours entries.
        $len = max(count($tempTimes), count($humTimes), count($windTimes));
        if ($len === 0) return [];
        $slice = min($hours, $len);
        $start = max(0, $len - $slice);
        for ($i = $start; $i < $len; $i++) {
            $temp = $this->extractBmkgValue($tempTimes[$i] ?? null);
            $hum = $this->extractBmkgValue($humTimes[$i] ?? null);
            $wind = $this->extractBmkgValue($windTimes[$i] ?? null);
            $condRaw = $this->extractBmkgValue($condTimes[$i] ?? null, true);
            $timeStr = $this->extractBmkgTime($tempTimes[$i] ?? $humTimes[$i] ?? $windTimes[$i] ?? null);
            $cond = $this->mapBmkgCondition($condRaw);
            $series[] = [
                'time' => $timeStr ? Carbon::parse($timeStr, 'Asia/Jakarta')->toIso8601String() : now()->toIso8601String(),
                'temperature' => $temp !== null ? (float)$temp : null,
                'humidity' => $hum !== null ? (int)$hum : null,
                'wind_speed' => $wind !== null ? (float)$wind : null,
                'light_lux' => $this->estimateLux($cond),
                'condition' => $cond,
            ];
        }
        return $series;
    }

    private function extractBmkgValue($entry, bool $string = false)
    {
        // Entry could be ['time' => '...', 'value' => '30'] or similar
        if (is_array($entry)) {
            if (array_key_exists('value', $entry)) return $string ? $entry['value'] : (is_numeric($entry['value']) ? +$entry['value'] : null);
            if (array_key_exists('val', $entry)) return $string ? $entry['val'] : (is_numeric($entry['val']) ? +$entry['val'] : null);
        }
        return $string ? (string)$entry : (is_numeric($entry) ? +$entry : null);
    }

    private function extractBmkgTime($entry): ?string
    {
        if (is_array($entry)) {
            if (isset($entry['time'])) return $entry['time'];
            if (isset($entry['datetime'])) return $entry['datetime'];
        }
        return null;
    }

    private function mapBmkgCondition(?string $raw): string
    {
        if (!$raw) return 'cerah';
        $map = config('weather.bmkg.condition_map', []);
        foreach ($map as $k => $v) {
            if (stripos($raw, $k) !== false) return $v;
        }
        // Simple heuristics
        if (stripos($raw, 'hujan') !== false) return 'hujan';
        if (stripos($raw, 'awan') !== false) return 'mendung';
        return 'cerah';
    }
}
