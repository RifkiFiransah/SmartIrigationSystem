<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WaterStorage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class WaterStorageController extends Controller
{
    /**
     * Get all water storages
     */
    public function index(): JsonResponse
    {
        $waterStorages = WaterStorage::with('device')->get()->map(function ($storage) {
            return [
                'id' => $storage->id,
                'tank_name' => $storage->tank_name,
                'device_name' => $storage->device?->device_name,
                'total_capacity' => $storage->total_capacity,
                'current_volume' => $storage->current_volume,
                'percentage' => $storage->percentage,
                'status' => $storage->status,
                'updated_at' => $storage->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $waterStorages
        ]);
    }

    /**
     * Update water volume for a specific tank
     */
    public function updateVolume(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'tank_id' => 'required|exists:water_storages,id',
                'current_volume' => 'required|numeric|min:0',
            ]);

            $waterStorage = WaterStorage::findOrFail($validated['tank_id']);
            
            // Pastikan volume tidak melebihi kapasitas
            $newVolume = min($validated['current_volume'], $waterStorage->total_capacity);
            
            $waterStorage->current_volume = $newVolume;
            
            // Auto update status berdasarkan persentase
            $percentage = $waterStorage->percentage;
            if ($percentage >= 90) {
                $waterStorage->status = 'full';
            } elseif ($percentage <= 10) {
                $waterStorage->status = 'empty';
            } elseif ($percentage <= 25) {
                $waterStorage->status = 'low';
            } else {
                $waterStorage->status = 'normal';
            }
            
            $waterStorage->save();

            return response()->json([
                'success' => true,
                'message' => 'Water volume updated successfully',
                'data' => [
                    'id' => $waterStorage->id,
                    'tank_name' => $waterStorage->tank_name,
                    'current_volume' => $waterStorage->current_volume,
                    'percentage' => $waterStorage->percentage,
                    'status' => $waterStorage->status,
                ]
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
                'message' => 'Failed to update water volume',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get water storage by tank name
     */
    public function getByTankName(string $tankName): JsonResponse
    {
        $waterStorage = WaterStorage::with('device')
            ->where('tank_name', $tankName)
            ->first();

        if (!$waterStorage) {
            return response()->json([
                'success' => false,
                'message' => 'Water storage not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $waterStorage->id,
                'tank_name' => $waterStorage->tank_name,
                'device_name' => $waterStorage->device?->device_name,
                'total_capacity' => $waterStorage->total_capacity,
                'current_volume' => $waterStorage->current_volume,
                'percentage' => $waterStorage->percentage,
                'status' => $waterStorage->status,
                'notes' => $waterStorage->notes,
                'updated_at' => $waterStorage->updated_at,
            ]
        ]);
    }
    
    /**
     * GET /api/water-storage/daily-usage?tank_id=1&days=30
     * Jika tank_id tidak dikirim dan hanya satu tangki, pakai yang pertama.
     */
    public function dailyUsage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tank_id' => 'nullable|integer|exists:water_storages,id',
            'days' => 'nullable|integer|min:1|max:180',
        ]);

        $days = $validated['days'] ?? 30;
        $query = WaterStorage::query();
        if (isset($validated['tank_id'])) {
            $query->where('id', $validated['tank_id']);
        }
        $storage = $query->first();
        if (!$storage) {
            return response()->json([
                'success' => false,
                'message' => 'Water storage not found'
            ], 404);
        }

        $data = $storage->getDailyUsage($days);
        return response()->json([
            'success' => true,
            'tank_id' => $storage->id,
            'tank_name' => $storage->tank_name,
            'days' => $days,
            'data' => $data,
        ]);
    }
}
