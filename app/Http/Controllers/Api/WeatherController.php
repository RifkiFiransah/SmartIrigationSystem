<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BMKGWeatherService;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function external(Request $request, BMKGWeatherService $service)
    {
        $lat = (float) ($request->query('lat', -6.2000)); // default Jakarta
        $lon = (float) ($request->query('lon', 106.8166));
        if ($provider = $request->query('provider')) {
            $this->mutateProvider($service, $provider);
        }
        $data = $service->getWeather($lat, $lon);
        return response()->json($data);
    }

    public function hourly(Request $request, BMKGWeatherService $service)
    {
        $lat = (float) ($request->query('lat', -6.2000));
        $lon = (float) ($request->query('lon', 106.8166));
        $hours = (int) $request->query('hours', 24);
        if ($provider = $request->query('provider')) {
            $this->mutateProvider($service, $provider);
        }
        $data = $service->getHourly($lat, $lon, $hours);
        return response()->json($data);
    }

    private function mutateProvider(BMKGWeatherService $service, string $provider): void
    {
        // Simple reflection hack as provider is private; alternative: refactor service to accept dynamic provider param.
        try {
            $ref = new \ReflectionClass($service);
            if ($ref->hasProperty('provider')) {
                $prop = $ref->getProperty('provider');
                $prop->setAccessible(true);
                $prop->setValue($service, $provider);
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
