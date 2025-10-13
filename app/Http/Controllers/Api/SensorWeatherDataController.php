<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorWeatherData;
use Illuminate\Http\Request;

class SensorWeatherDataController extends Controller
{
    public function index()
    {
        try {
            $data = SensorWeatherData::orderBy('id', 'desc')->get();
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $rec = SensorWeatherData::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $rec
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
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
            return response()->json([
                'success' => true,
                'data' => $rec
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
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
            return response()->json([
                'success' => true,
                'data' => $rec
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $rec = SensorWeatherData::findOrFail($id);
            $rec->delete();
            return response()->json([
                'success' => true,
                'message' => 'Data deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}