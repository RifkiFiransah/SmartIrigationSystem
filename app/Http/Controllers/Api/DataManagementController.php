<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorData;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DataManagementController extends Controller
{
    /**
     * Clear all sensor data
     */
    public function clearSensorData(Request $request): JsonResponse
    {
        try {
            $count = SensorData::count();
            
            if ($count == 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'No sensor data to clear',
                    'cleared_count' => 0
                ]);
            }

            // Clear all sensor data
            SensorData::truncate();
            
            // Reset auto increment
            DB::statement('ALTER TABLE sensor_data AUTO_INCREMENT = 1');

            return response()->json([
                'success' => true,
                'message' => 'All sensor data cleared successfully',
                'cleared_count' => $count
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear sensor data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get data statistics
     */
    public function getDataStats(): JsonResponse
    {
        try {
            $stats = [
                'sensor_data_count' => SensorData::count(),
                'latest_data' => SensorData::latest('recorded_at')->first(),
                'oldest_data' => SensorData::oldest('recorded_at')->first(),
                'device_counts' => SensorData::select('device_id', DB::raw('count(*) as count'))
                    ->groupBy('device_id')
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Data statistics retrieved successfully',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get data statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear old sensor data (older than specified days)
     */
    public function clearOldData(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'required|integer|min:1'
        ]);

        try {
            $days = $request->days;
            $cutoffDate = now()->subDays($days);
            
            $count = SensorData::where('recorded_at', '<', $cutoffDate)->count();
            
            if ($count == 0) {
                return response()->json([
                    'success' => true,
                    'message' => "No sensor data older than {$days} days found",
                    'cleared_count' => 0
                ]);
            }

            // Delete old data
            SensorData::where('recorded_at', '<', $cutoffDate)->delete();

            return response()->json([
                'success' => true,
                'message' => "Sensor data older than {$days} days cleared successfully",
                'cleared_count' => $count
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear old sensor data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
