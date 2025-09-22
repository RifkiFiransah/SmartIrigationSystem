<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorData;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class SensorDataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $sensorData = SensorData::with('device')->latest()->paginate(20);
        
        return response()->json([
            'success' => true,
            'message' => 'Sensor data retrieved successfully',
            'data' => $sensorData
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'device_id' => 'required|exists:devices,id',
                'temperature' => 'nullable|numeric|between:-50,100',
                'humidity' => 'nullable|numeric|between:0,100',
                'soil_moisture' => 'nullable|numeric|between:0,100',
                'water_flow' => 'nullable|numeric|min:0',
                'water_height_cm' => 'nullable|numeric|min:0',
                'light_lux' => 'nullable|numeric|min:0',
                'temperature_c' => 'nullable|numeric|between:-50,100',
                'soil_moisture_pct' => 'nullable|integer|between:0,100',
                'recorded_at' => 'nullable|date',
                'status' => 'nullable|in:normal,alert,critical'
            ]);

            // Set default values
            $validated['recorded_at'] = $validated['recorded_at'] ?? now();
            $validated['status'] = $validated['status'] ?? 'normal';

            // Create sensor data
            $sensorData = SensorData::create($validated);

            // Load device relationship
            $sensorData->load('device');

            return response()->json([
                'success' => true,
                'message' => 'Sensor data created successfully',
                'data' => $sensorData
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create sensor data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SensorData $sensorReading): JsonResponse
    {
        $sensorReading->load('device');
        
        return response()->json([
            'success' => true,
            'message' => 'Sensor data retrieved successfully',
            'data' => $sensorReading
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SensorData $sensorReading): JsonResponse
    {
        try {
            $validated = $request->validate([
                'device_id' => 'sometimes|exists:devices,id',
                'temperature' => 'nullable|numeric|between:-50,100',
                'humidity' => 'nullable|numeric|between:0,100',
                'soil_moisture' => 'nullable|numeric|between:0,100',
                'water_flow' => 'nullable|numeric|min:0',
                'water_height_cm' => 'nullable|numeric|min:0',
                'light_lux' => 'nullable|numeric|min:0',
                'temperature_c' => 'nullable|numeric|between:-50,100',
                'soil_moisture_pct' => 'nullable|integer|between:0,100',
                'recorded_at' => 'nullable|date',
                'status' => 'nullable|in:normal,alert,critical'
            ]);

            $sensorReading->update($validated);
            $sensorReading->load('device');

            return response()->json([
                'success' => true,
                'message' => 'Sensor data updated successfully',
                'data' => $sensorReading
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sensor data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SensorData $sensorReading): JsonResponse
    {
        try {
            $sensorReading->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sensor data deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete sensor data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get latest sensor readings for frontend display
     */
    public function latest(Request $request): JsonResponse
    {
        try {
            $latest = SensorData::with('device')
                ->orderBy('recorded_at', 'desc')
                ->first();

            if (!$latest) {
                return response()->json([
                    'success' => false,
                    'message' => 'No sensor data available'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Latest sensor data retrieved successfully',
                'data' => $latest
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve latest sensor data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get hourly sensor data for charts
     */
    public function hourlyData(Request $request): JsonResponse
    {
        try {
            $hours = $request->input('hours', 24);
            
            $data = SensorData::select(
                DB::raw('HOUR(recorded_at) as hour'),
                DB::raw('DATE(recorded_at) as date'),
                DB::raw('AVG(temperature) as avg_temperature'),
                DB::raw('AVG(humidity) as avg_humidity'),
                DB::raw('AVG(soil_moisture) as avg_soil_moisture'),
                DB::raw('AVG(water_flow) as avg_water_flow'),
                DB::raw('COUNT(*) as reading_count')
            )
            ->where('recorded_at', '>=', now()->subHours($hours))
            ->groupBy(DB::raw('DATE(recorded_at)'), DB::raw('HOUR(recorded_at)'))
            ->orderBy('date')
            ->orderBy('hour')
            ->get();

            return response()->json([
                'success' => true,
                'message' => 'Hourly sensor data retrieved successfully',
                'data' => $data,
                'period' => "{$hours} hours"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve hourly data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get daily sensor data for weekly charts
     */
    public function dailyData(Request $request): JsonResponse
    {
        try {
            $days = $request->input('days', 7);
            
            $data = SensorData::select(
                DB::raw('DATE(recorded_at) as date'),
                DB::raw('AVG(temperature) as avg_temperature'),
                DB::raw('AVG(humidity) as avg_humidity'),
                DB::raw('AVG(soil_moisture) as avg_soil_moisture'),
                DB::raw('AVG(water_flow) as avg_water_flow'),
                DB::raw('MIN(temperature) as min_temperature'),
                DB::raw('MAX(temperature) as max_temperature'),
                DB::raw('COUNT(*) as reading_count')
            )
            ->where('recorded_at', '>=', now()->subDays($days))
            ->groupBy(DB::raw('DATE(recorded_at)'))
            ->orderBy('date')
            ->get();

            return response()->json([
                'success' => true,
                'message' => 'Daily sensor data retrieved successfully',
                'data' => $data,
                'period' => "{$days} days"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve daily data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get latest sensor readings per device for node monitoring
     */
    public function latestPerDevice(Request $request): JsonResponse
    {
        try {
            // NOTE: After schema redesign (Sept 2025) we standardized per-device fields:
            //  - ground_temperature_c (replaces temperature/temperature_c)
            //  - soil_moisture_pct (primary moisture metric)
            //  - battery_voltage_v (simple battery) OR derive from INA226 bus voltage if present
            // Provide backward compatible aliases expected by frontend (temperature_c, temperature, soil_moisture).
            $latestPerDevice = DB::table('sensor_data as sd1')
                ->select([
                    'sd1.device_id',
                    'devices.device_name',
                    'devices.location',
                    DB::raw('sd1.ground_temperature_c as temperature_c'),
                    DB::raw('sd1.ground_temperature_c as temperature'),
                    'sd1.humidity',
                    DB::raw('sd1.soil_moisture_pct as soil_moisture'),
                    'sd1.soil_moisture_pct',
                    'sd1.water_height_cm',
                    'sd1.irrigation_usage_total_l',
                    DB::raw('COALESCE(sd1.battery_voltage_v, sd1.ina226_bus_voltage_v) AS battery_voltage_v'),
                    'sd1.ina226_power_mw',
                    // Aggregate water usage logs (today) per device
                    DB::raw('(
                        SELECT COALESCE(
                            (SELECT SUM(volume_used_l) FROM water_usage_logs w1 WHERE w1.device_id = sd1.device_id AND w1.usage_date = CURDATE()),
                            (SELECT SUM(volume_used_l) FROM water_usage_logs w2 WHERE w2.device_id = sd1.device_id AND w2.usage_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY))
                        )
                    ) AS water_usage_today_l'),
                    'sd1.status',
                    'sd1.recorded_at'
                ])
                ->join('devices', 'devices.id', '=', 'sd1.device_id')
                ->whereRaw('sd1.recorded_at = (
                    SELECT MAX(sd2.recorded_at) 
                    FROM sensor_data sd2 
                    WHERE sd2.device_id = sd1.device_id
                )')
                ->where('devices.is_active', true)
                ->orderBy('sd1.device_id')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Latest sensor data per device retrieved successfully',
                'data' => $latestPerDevice
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve latest sensor data per device',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
