<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WaterStorage;
use App\Models\Device;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    /**
     * Get zones summary with their tanks and devices
     */
    public function getZonesSummary()
    {
        try {
            // Group water storages by zone
            $zones = WaterStorage::select(['zone_name', 'zone_description'])
                ->whereNotNull('zone_name')
                ->distinct('zone_name')
                ->get()
                ->map(function ($zone) {
                    // Get all tanks in this zone
                    $tanks = WaterStorage::where('zone_name', $zone->zone_name)->get();
                    
                    // Calculate zone statistics
                    $totalCapacity = $tanks->sum('total_capacity');
                    $currentVolume = $tanks->sum('current_volume');
                    $percentage = $totalCapacity > 0 ? ($currentVolume / $totalCapacity) * 100 : 0;
                    
                    // Get unique devices in this zone
                    $deviceIds = $tanks->pluck('device_id')->filter()->unique();
                    $devices = Device::whereIn('id', $deviceIds)->get();
                    
                    // Count associated devices
                    $associatedDevicesCount = $tanks->flatMap(function ($tank) {
                        return $tank->associated_devices ?? [];
                    })->count();
                    
                    // Determine zone status
                    $criticalTanks = $tanks->where('status', 'empty')->count();
                    $lowTanks = $tanks->where('status', 'low')->count();
                    $zoneStatus = 'normal';
                    
                    if ($criticalTanks > 0) {
                        $zoneStatus = 'critical';
                    } elseif ($lowTanks > 0) {
                        $zoneStatus = 'warning';
                    } elseif ($percentage >= 80) {
                        $zoneStatus = 'excellent';
                    }
                    
                    return [
                        'zone_name' => $zone->zone_name,
                        'zone_description' => $zone->zone_description,
                        'total_tanks' => $tanks->count(),
                        'total_capacity' => round($totalCapacity, 2),
                        'current_volume' => round($currentVolume, 2),
                        'percentage' => round($percentage, 2),
                        'zone_status' => $zoneStatus,
                        'primary_devices' => $devices->count(),
                        'associated_devices' => $associatedDevicesCount,
                        'total_nodes' => $devices->count() + $associatedDevicesCount,
                        'tanks' => $tanks->map(function ($tank) {
                            return [
                                'id' => $tank->id,
                                'tank_name' => $tank->tank_name,
                                'capacity' => $tank->total_capacity,
                                'current' => $tank->current_volume,
                                'percentage' => $tank->percentage,
                                'status' => $tank->status,
                                'max_daily_usage' => $tank->max_daily_usage,
                                'days_until_empty' => $tank->days_until_empty,
                                'device_name' => $tank->device?->device_name,
                            ];
                        }),
                        'devices' => $devices->map(function ($device) {
                            return [
                                'id' => $device->id,
                                'device_name' => $device->device_name,
                                'device_id' => $device->device_id,
                                'location' => $device->location,
                                'status' => $device->status ?? 'unknown',
                            ];
                        }),
                    ];
                });
                
            return response()->json([
                'success' => true,
                'data' => [
                    'zones' => $zones,
                    'summary' => [
                        'total_zones' => $zones->count(),
                        'total_tanks' => WaterStorage::count(),
                        'total_devices' => Device::count(),
                        'critical_zones' => $zones->where('zone_status', 'critical')->count(),
                        'warning_zones' => $zones->where('zone_status', 'warning')->count(),
                        'overall_capacity' => round($zones->sum('total_capacity'), 2),
                        'overall_volume' => round($zones->sum('current_volume'), 2),
                        'overall_percentage' => $zones->sum('total_capacity') > 0 ? 
                            round(($zones->sum('current_volume') / $zones->sum('total_capacity')) * 100, 2) : 0,
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching zones data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get specific zone details
     */
    public function getZoneDetails($zoneName)
    {
        try {
            $tanks = WaterStorage::where('zone_name', $zoneName)->get();
            
            if ($tanks->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zone not found'
                ], 404);
            }
            
            $zone = $tanks->first();
            
            // Get all devices in this zone
            $allDevices = collect();
            
            foreach ($tanks as $tank) {
                // Add primary device
                if ($tank->device) {
                    $allDevices->push([
                        'device' => $tank->device,
                        'role' => 'Primary Node (Tank: ' . $tank->tank_name . ')',
                        'tank_id' => $tank->id
                    ]);
                }
                
                // Add associated devices
                if ($tank->associated_devices) {
                    foreach ($tank->associated_devices as $assocDevice) {
                        $device = Device::find($assocDevice['device_id']);
                        if ($device) {
                            $allDevices->push([
                                'device' => $device,
                                'role' => $assocDevice['role'] ?? 'Additional Node',
                                'tank_id' => $tank->id
                            ]);
                        }
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'zone_name' => $zoneName,
                    'zone_description' => $zone->zone_description,
                    'tanks' => $tanks,
                    'devices' => $allDevices->unique('device.id'),
                    'statistics' => [
                        'total_tanks' => $tanks->count(),
                        'total_capacity' => $tanks->sum('total_capacity'),
                        'current_volume' => $tanks->sum('current_volume'),
                        'total_devices' => $allDevices->unique('device.id')->count(),
                        'estimated_daily_usage' => $tanks->sum('max_daily_usage'),
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching zone details: ' . $e->getMessage()
            ], 500);
        }
    }
}
