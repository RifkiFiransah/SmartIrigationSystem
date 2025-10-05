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
            // Get all active devices first
            $devices = Device::where('is_active', true)->get();
            
            $latestPerDevice = [];
            
            foreach ($devices as $device) {
                // Get latest sensor data for each device
                $latestData = SensorData::where('device_id', $device->id)
                    ->orderBy('recorded_at', 'desc')
                    ->first();
                
                if ($latestData) {
                    // Get today's water usage for this device
                    $waterUsageToday = DB::table('water_usage_logs')
                        ->where('device_id', $device->id)
                        ->whereDate('usage_date', now()->toDateString())
                        ->sum('volume_used_l') ?? 0;
                    
                    $latestPerDevice[] = [
                        'device_id' => $device->id,
                        'device_name' => $device->device_name,
                        'location' => $device->location,
                        'temperature_c' => $latestData->ground_temperature_c ?? $latestData->temperature_c ?? $latestData->temperature,
                        'ground_temperature_c' => $latestData->ground_temperature_c ?? $latestData->temperature_c,
                        'temperature' => $latestData->ground_temperature_c ?? $latestData->temperature_c ?? $latestData->temperature,
                        'humidity' => $latestData->humidity,
                        'soil_moisture' => $latestData->soil_moisture_pct ?? $latestData->soil_moisture,
                        'soil_moisture_pct' => $latestData->soil_moisture_pct,
                        'water_height_cm' => $latestData->water_height_cm,
                        'light_lux' => $latestData->light_lux,
                        'wind_speed_ms' => $latestData->wind_speed_ms,
                        'irrigation_usage_total_l' => $latestData->irrigation_usage_total_l,
                        'battery_voltage_v' => $latestData->battery_voltage_v ?? $latestData->ina226_bus_voltage_v,
                        'ina226_power_mw' => $latestData->ina226_power_mw,
                        'water_usage_today_l' => $waterUsageToday,
                        'status' => $latestData->status,
                        'recorded_at' => $latestData->recorded_at,
                        // ✅ Add device connection and valve state from devices table
                        'connection_state' => $device->connection_state ?? 'offline',
                        'valve_state' => $device->valve_state ?? 'closed',
                        'is_active' => $device->is_active,
                        'last_seen_at' => $device->last_seen_at,
                    ];
                } else {
                    // No sensor data for this device yet
                    $latestPerDevice[] = [
                        'device_id' => $device->id,
                        'device_name' => $device->device_name,
                        'location' => $device->location,
                        'temperature_c' => null,
                        'ground_temperature_c' => null,
                        'temperature' => null,
                        'humidity' => null,
                        'soil_moisture' => null,
                        'soil_moisture_pct' => null,
                        'water_height_cm' => null,
                        'light_lux' => null,
                        'wind_speed_ms' => null,
                        'irrigation_usage_total_l' => null,
                        'battery_voltage_v' => null,
                        'ina226_power_mw' => null,
                        'water_usage_today_l' => 0,
                        'status' => 'no_data',
                        'recorded_at' => null,
                        // ✅ Add device connection and valve state from devices table even when no sensor data
                        'connection_state' => $device->connection_state ?? 'offline',
                        'valve_state' => $device->valve_state ?? 'closed',
                        'is_active' => $device->is_active,
                        'last_seen_at' => $device->last_seen_at,
                    ];
                }
            }

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
