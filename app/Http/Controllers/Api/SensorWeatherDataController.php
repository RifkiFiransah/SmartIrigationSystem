<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorWeatherData;
use Illuminate\Http\Request;

class SensorWeatherDataController extends Controller
{
    public function index()
    {
        return response()->json(SensorWeatherData::latest()->get());
    }

    public function show($id)
    {
        $rec = SensorWeatherData::findOrFail($id);
        return response()->json($rec);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id' => 'sometimes|integer',
            'sesi_id_getdata' => 'required|integer',
            'node_id' => 'required|integer',
            'voltage' => 'nullable|numeric',
            'current' => 'nullable|numeric',
            'power' => 'nullable|numeric',
            'light' => 'nullable|numeric',
            'rain' => 'nullable|numeric',
            'rain_adc' => 'nullable|integer',
            'wind' => 'nullable|numeric',
            'wind_pulse' => 'nullable|integer',
            'humidity' => 'nullable|numeric',
            'temp_dht' => 'nullable|numeric',
            'rssi' => 'nullable|numeric',
            'snr' => 'nullable|numeric',
            'signal_quality' => 'nullable|string',
        ]);

        $rec = SensorWeatherData::create($data);
        return response()->json($rec, 201);
    }

    public function update(Request $request, $id)
    {
        $rec = SensorWeatherData::findOrFail($id);
        $data = $request->validate([
            'sesi_id_getdata' => 'sometimes|integer',
            'node_id' => 'sometimes|integer',
            'voltage' => 'nullable|numeric',
            'current' => 'nullable|numeric',
            'power' => 'nullable|numeric',
            'light' => 'nullable|numeric',
            'rain' => 'nullable|numeric',
            'rain_adc' => 'nullable|integer',
            'wind' => 'nullable|numeric',
            'wind_pulse' => 'nullable|integer',
            'humidity' => 'nullable|numeric',
            'temp_dht' => 'nullable|numeric',
            'rssi' => 'nullable|numeric',
            'snr' => 'nullable|numeric',
            'signal_quality' => 'nullable|string',
        ]);

        $rec->update($data);
        return response()->json($rec);
    }

    public function destroy($id)
    {
        $rec = SensorWeatherData::findOrFail($id);
        $rec->delete();
        return response()->json(null, 204);
    }
}
