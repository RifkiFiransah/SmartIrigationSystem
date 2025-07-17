<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorData;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

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
}
