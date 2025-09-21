<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class BMKGForecastController extends Controller
{
    /**
     * Return flattened BMKG forecast entries for given adm4 (default configured region).
     * Cache for 10 minutes.
     */
    public function index(Request $request)
    {
        $adm4 = $request->query('adm4', config('irrigation.bmkg_adm4', '32.08.10.2001'));
        $cacheKey = 'bmkg_forecast_'.$adm4;
        $data = Cache::remember($cacheKey, 600, function() use ($adm4) {
            $url = 'https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4='.$adm4;
            try {
                $resp = Http::timeout(8)->get($url);
                if (!$resp->ok()) return [];
                $raw = $resp->json();
                $blocks = $raw['data'][0]['cuaca'] ?? [];
                $flat = [];
                foreach ($blocks as $block) {
                    if (is_array($block)) {
                        foreach ($block as $entry) {
                            if (!is_array($entry)) continue;
                            // Normalize keys
                            $flat[] = [
                                'local_datetime' => $entry['local_datetime'] ?? ($entry['datetime'] ?? null),
                                'datetime_utc' => $entry['utc_datetime'] ?? null,
                                't' => $entry['t'] ?? null,
                                'humidity' => $entry['hu'] ?? ($entry['h'] ?? null),
                                'tcc' => $entry['tcc'] ?? null, // Already 0-100 from sample
                                'rain' => $entry['tp'] ?? null,
                                'weather_code' => $entry['weather'] ?? null,
                                'weather_desc' => $entry['weather_desc'] ?? null,
                                'weather_icon' => $entry['image'] ?? null,
                                'wind_speed_ms' => $entry['ws'] ?? null,
                                'wind_dir_cardinal' => $entry['wd'] ?? null,
                                'wind_dir_deg' => $entry['wd_deg'] ?? null,
                            ];
                        }
                    }
                }
                usort($flat, fn($a,$b)=>strtotime($a['local_datetime']) <=> strtotime($b['local_datetime']));
                return $flat;
            } catch (\Throwable $e) {
                return [];
            }
        });

        return response()->json(['entries' => $data, 'adm4' => $adm4]);
    }
}
