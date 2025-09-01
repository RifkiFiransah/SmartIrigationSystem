<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorData;
use App\Models\WaterStorage;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class DataTransferController extends Controller
{
    /**
     * Endpoint untuk hardware mengirim data sensor via MQTT
     * POST /api/transfer/sensor-data
     */
    public function storeSensorData(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'device_id' => 'required|string|max:50',
                'temperature' => 'required|numeric|between:-50,100',
                'humidity' => 'required|numeric|between:0,100',
                'soil_moisture' => 'required|numeric|between:0,100',
                'water_flow' => 'required|numeric|min:0',
                'status' => 'nullable|string|in:normal,low,high,critical',
                'timestamp' => 'nullable|date'
            ]);

            // Cari atau buat device
            $device = Device::firstOrCreate(
                ['device_id' => $validated['device_id']],
                [
                    'device_name' => 'Hardware Device ' . $validated['device_id'],
                    'location' => 'Unknown Location',
                    'is_active' => true
                ]
            );

            // Tentukan status otomatis jika tidak disediakan
            if (!isset($validated['status'])) {
                $validated['status'] = $this->determineStatus($validated);
            }

            // Simpan data sensor
            $sensorData = SensorData::create([
                'device_id' => $device->id,
                'temperature' => $validated['temperature'],
                'humidity' => $validated['humidity'],
                'soil_moisture' => $validated['soil_moisture'],
                'water_flow' => $validated['water_flow'],
                'status' => $validated['status'],
                'recorded_at' => isset($validated['timestamp']) ? 
                    Carbon::parse($validated['timestamp']) : now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sensor data received successfully',
                'data' => [
                    'id' => $sensorData->id,
                    'device_id' => $validated['device_id'],
                    'status' => $validated['status'],
                    'recorded_at' => $sensorData->recorded_at->toISOString(),
                    'server_time' => now()->toISOString()
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data format',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to store sensor data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint untuk hardware mengirim data water storage via MQTT
     * POST /api/transfer/water-level
     */
    public function storeWaterLevel(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'tank_id' => 'required|integer|exists:water_storages,id',
                'current_volume' => 'required|numeric|min:0',
                'sensor_reading' => 'nullable|numeric', // Raw sensor value
                'timestamp' => 'nullable|date'
            ]);

            $waterStorage = WaterStorage::findOrFail($validated['tank_id']);
            
            // Pastikan volume tidak melebihi kapasitas
            $newVolume = min($validated['current_volume'], $waterStorage->total_capacity);
            
            // Update volume dan status
            $waterStorage->current_volume = $newVolume;
            
            // Auto-update status berdasarkan persentase
            $percentage = ($newVolume / $waterStorage->total_capacity) * 100;
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
                'message' => 'Water level updated successfully',
                'data' => [
                    'tank_id' => $waterStorage->id,
                    'tank_name' => $waterStorage->tank_name,
                    'current_volume' => $waterStorage->current_volume,
                    'total_capacity' => $waterStorage->total_capacity,
                    'percentage' => round($percentage, 2),
                    'status' => $waterStorage->status,
                    'updated_at' => $waterStorage->updated_at->toISOString(),
                    'server_time' => now()->toISOString()
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data format',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update water level',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint untuk hardware mendapatkan konfigurasi/perintah
     * GET /api/transfer/device-config/{device_id}
     */
    public function getDeviceConfig(string $deviceId): JsonResponse
    {
        try {
            $device = Device::where('device_id', $deviceId)->first();
            
            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found',
                    'config' => $this->getDefaultConfig()
                ], 404);
            }

            // Ambil konfigurasi dari database atau default
            $config = [
                'device_id' => $device->device_id,
                'device_name' => $device->device_name,
                'location' => $device->location,
                'is_active' => $device->is_active,
                'settings' => [
                    'sampling_interval' => 30, // seconds
                    'transmission_interval' => 60, // seconds
                    'temperature_threshold' => [
                        'min' => 15,
                        'max' => 35,
                        'critical_min' => 10,
                        'critical_max' => 40
                    ],
                    'humidity_threshold' => [
                        'min' => 30,
                        'max' => 80,
                        'critical_min' => 20,
                        'critical_max' => 90
                    ],
                    'soil_moisture_threshold' => [
                        'min' => 30,
                        'max' => 80,
                        'critical_min' => 20,
                        'critical_max' => 90
                    ],
                    'water_flow_threshold' => [
                        'min' => 0,
                        'normal' => 50,
                        'max' => 1000
                    ]
                ],
                'commands' => [
                    'irrigation_mode' => 'auto', // auto, manual, off
                    'irrigation_duration' => 300, // seconds
                    'emergency_stop' => false
                ],
                'server_time' => now()->toISOString()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Device configuration retrieved',
                'config' => $config
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get device configuration',
                'error' => $e->getMessage(),
                'config' => $this->getDefaultConfig()
            ], 500);
        }
    }

    /**
     * Endpoint untuk hardware mendapatkan status sistem terbaru
     * GET /api/transfer/system-status
     */
    public function getSystemStatus(): JsonResponse
    {
        try {
            // Ambil data sensor terbaru
            $latestSensorData = SensorData::with('device')
                ->orderBy('recorded_at', 'desc')
                ->take(10)
                ->get();

            // Ambil data water storage
            $waterStorages = WaterStorage::all();

            // Hitung statistik sistem
            $systemStats = [
                'total_devices' => Device::where('is_active', true)->count(),
                'online_devices' => SensorData::whereDate('recorded_at', today())->distinct('device_id')->count(),
                'latest_reading_time' => $latestSensorData->first()?->recorded_at?->toISOString(),
                'total_water_tanks' => $waterStorages->count(),
                'low_water_tanks' => $waterStorages->where('status', 'low')->count() + $waterStorages->where('status', 'empty')->count(),
                'system_health' => $this->calculateSystemHealth($latestSensorData, $waterStorages)
            ];

            return response()->json([
                'success' => true,
                'message' => 'System status retrieved',
                'data' => [
                    'system_stats' => $systemStats,
                    'latest_readings' => $latestSensorData->map(function ($reading) {
                        return [
                            'device_id' => $reading->device->device_id,
                            'device_name' => $reading->device->device_name,
                            'temperature' => $reading->temperature,
                            'humidity' => $reading->humidity,
                            'soil_moisture' => $reading->soil_moisture,
                            'water_flow' => $reading->water_flow,
                            'status' => $reading->status,
                            'recorded_at' => $reading->recorded_at->toISOString()
                        ];
                    }),
                    'water_storage_summary' => [
                        'total_capacity' => $waterStorages->sum('total_capacity'),
                        'current_volume' => $waterStorages->sum('current_volume_liters'),
                        'average_percentage' => $waterStorages->count() > 0 ? 
                            round($waterStorages->avg(function ($storage) {
                                return ($storage->current_volume / $storage->total_capacity) * 100;
                            }), 2) : 0
                    ],
                    'server_time' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get system status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint untuk hardware ping/heartbeat
     * POST /api/transfer/heartbeat
     */
    public function heartbeat(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'device_id' => 'required|string|max:50',
                'firmware_version' => 'nullable|string|max:20',
                'ip_address' => 'nullable|ip',
                'signal_strength' => 'nullable|integer|between:-100,0'
            ]);

            // Update atau buat device heartbeat
            $device = Device::where('device_id', $validated['device_id'])->first();
            
            if ($device) {
                $device->touch(); // Update timestamp
            }

            return response()->json([
                'success' => true,
                'message' => 'Heartbeat received',
                'data' => [
                    'device_id' => $validated['device_id'],
                    'server_time' => now()->toISOString(),
                    'next_heartbeat' => now()->addMinutes(5)->toISOString()
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid heartbeat data',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Heartbeat failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method untuk menentukan status berdasarkan nilai sensor
     */
    private function determineStatus(array $data): string
    {
        $temp = $data['temperature'];
        $humidity = $data['humidity'];
        $soilMoisture = $data['soil_moisture'];

        // Kondisi kritis
        if ($temp < 10 || $temp > 40 || $humidity < 20 || $humidity > 90 || $soilMoisture < 20) {
            return 'critical';
        }

        // Kondisi rendah/tinggi (peringatan)
        if ($temp < 15 || $temp > 35 || $humidity < 30 || $humidity > 80 || $soilMoisture < 30) {
            return 'low';
        }

        return 'normal';
    }

    /**
     * Helper method untuk konfigurasi default
     */
    private function getDefaultConfig(): array
    {
        return [
            'device_id' => 'unknown',
            'settings' => [
                'sampling_interval' => 30,
                'transmission_interval' => 60
            ],
            'commands' => [
                'irrigation_mode' => 'manual'
            ]
        ];
    }

    /**
     * Helper method untuk menghitung kesehatan sistem
     */
    private function calculateSystemHealth($sensorData, $waterStorages): string
    {
        $issues = 0;
        
        // Cek sensor readings
        $criticalReadings = $sensorData->where('status', 'critical')->count();
        $lowReadings = $sensorData->where('status', 'low')->count();
        
        // Cek water storage
        $lowWaterTanks = $waterStorages->whereIn('status', ['low', 'empty'])->count();
        
        if ($criticalReadings > 0 || $lowWaterTanks > ($waterStorages->count() / 2)) {
            return 'critical';
        }
        
        if ($lowReadings > ($sensorData->count() / 2) || $lowWaterTanks > 0) {
            return 'warning';
        }
        
        return 'good';
    }
}
