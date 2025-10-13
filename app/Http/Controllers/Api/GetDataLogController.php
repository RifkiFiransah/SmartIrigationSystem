<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GetDataLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GetDataLogController extends Controller
{
    public function getCombinedData(Request $request)
    {
        try {
            // Ambil parameter filter jika ada
            $sesiId = $request->input('sesi_id');
            $limit = $request->input('limit'); // Jika tidak ada limit, ambil semua
            $date = $request->input('date'); // Filter by date

            // Query dengan filter
            $query = GetDataLog::with(['sensorNodeData.node', 'sensorWeatherData.node'])
                ->orderBy('waktu_mulai', 'desc');

            if ($sesiId) {
                $query->where('sesi_id_getdata', $sesiId);
            }

            if ($date) {
                $query->whereDate('waktu_mulai', $date);
            }

            // Ambil data sesuai limit atau semua
            $logs = $limit ? $query->limit($limit)->get() : $query->get();

            if ($logs->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data found'
                ], 404);
            }

            // Hitung total expected nodes
            $expectedNodes = 12; // Sesuaikan dengan jumlah node di sistem Anda

            // Format response untuk semua data logs
            $allData = $logs->map(function ($log) use ($expectedNodes) {
                $receivedNodes = $log->sensorNodeData->count();
                $completenessPercentage = $expectedNodes > 0
                    ? round(($receivedNodes / $expectedNodes) * 100, 2)
                    : 0;

                return [
                    'getdata_log' => [
                        'id' => $log->id,
                        'sesi_id_getdata' => $log->sesi_id_getdata,
                        'waktu_mulai' => $log->waktu_mulai,
                        'waktu_selesai' => $log->waktu_selesai,
                        'node_sukses' => $log->node_sukses ?? 0,
                        'node_gagal' => $log->node_gagal ?? 0,
                    ],
                    'sensor_weather_data' => $log->sensorWeatherData->map(function ($weather) {
                        return [
                            'id' => $weather->id,
                            'sesi_id_getdata' => $weather->sesi_id_getdata,
                            'node_id' => $weather->node_id,
                            'voltage' => $weather->voltage ?? 0,
                            'current' => $weather->current ?? 0,
                            'power' => $weather->power ?? 0,
                            'light' => $weather->light ?? 0,
                            'rain' => $weather->rain ?? 0,
                            'rain_adc' => $weather->rain_adc ?? 0,
                            'wind' => $weather->wind ?? 0,
                            'wind_pulse' => $weather->wind_pulse ?? 0,
                            'humidity' => $weather->humidity ?? 0,
                            'temp_dht' => $weather->temp_dht ?? 0,
                            'rssi' => $weather->rssi ?? 0,
                            'snr' => $weather->snr ?? 0,
                            'signal_quality' => $this->getSignalQuality($weather->rssi ?? 0, $weather->snr ?? 0),
                        ];
                    }),
                    'sensor_node_data' => $log->sensorNodeData->map(function ($node) {
                        return [
                            'id' => $node->id,
                            'sesi_id_getdata' => $node->sesi_id_getdata,
                            'node_id' => $node->node_id,
                            'rssi_dbm' => $node->rssi_dbm ?? 0,
                            'snr_db' => $node->snr_db ?? 0,
                            'voltage_v' => $node->voltage_v ?? 0,
                            'current_ma' => $node->current_ma ?? 0,
                            'power_mw' => $node->power_mw ?? 0,
                            'temp_c' => $node->temp_c ?? 0,
                            'soil_pct' => $node->soil_pct ?? 0,
                            'soil_adc' => $node->soil_adc ?? 0,
                            'ts_counter' => $node->ts_counter ?? 0,
                            'received_at' => $node->received_at ?? $node->created_at,
                        ];
                    }),
                    'data_completeness' => [
                        'expected_nodes' => $expectedNodes,
                        'received_nodes' => $receivedNodes,
                        'completeness_percentage' => $completenessPercentage . '%',
                    ]
                ];
            });

            // Hitung summary statistics
            $totalLogs = $logs->count();
            $totalWeatherData = $logs->sum(function ($log) {
                return $log->sensorWeatherData->count();
            });
            $totalNodeData = $logs->sum(function ($log) {
                return $log->sensorNodeData->count();
            });

            return response()->json([
                'success' => true,
                'message' => 'Sensor data retrieved successfully',
                'timestamp' => now()->toIso8601String(),
                'data' => $allData,
                'summary' => [
                    'total_sessions' => $totalLogs,
                    'total_weather_records' => $totalWeatherData,
                    'total_node_records' => $totalNodeData,
                    'expected_nodes_per_session' => $expectedNodes,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve sensor data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getSignalQuality($rssi, $snr)
    {
        if ($rssi > -50 && $snr > 20) {
            return 'Excellent';
        } elseif ($rssi >= -70 && $snr >= 15) {
            return 'Good';
        } elseif ($rssi >= -90 && $snr >= 10) {
            return 'Fair';
        } else {
            return 'Poor';
        }
    }

    public function getCombinedDatabyIdGetDataLog($id)
    {
        try {
            // Ambil data log dengan relasi
            $log = GetDataLog::with(['sensorNodeData.node', 'sensorWeatherData.node'])
                ->where('id', $id)
                ->firstOrFail();

            // Hitung total expected nodes
            $expectedNodes = 12; // Sesuaikan dengan jumlah node di sistem Anda
            $receivedNodes = $log->sensorNodeData->count();
            $completenessPercentage = $expectedNodes > 0
                ? round(($receivedNodes / $expectedNodes) * 100, 2)
                : 0;

            // Format response
            return response()->json([
                'success' => true,
                'message' => 'Sensor data by id getDataLog retrieved successfully',
                'timestamp' => now()->toIso8601String(),
                'data' => [
                    'getdata_log' => [
                        'id' => $log->id,
                        'sesi_id_getdata' => $log->sesi_id_getdata,
                        'waktu_mulai' => $log->waktu_mulai,
                        'waktu_selesai' => $log->waktu_selesai,
                        'node_sukses' => $log->node_sukses ?? 0,
                        'node_gagal' => $log->node_gagal ?? 0,
                    ],
                    'sensor_weather_data' => $log->sensorWeatherData->map(function ($weather) {
                        return [
                            'id' => $weather->id,
                            'sesi_id_getdata' => $weather->sesi_id_getdata,
                            'node_id' => $weather->node_id,
                            'voltage' => $weather->voltage ?? 0,
                            'current' => $weather->current ?? 0,
                            'power' => $weather->power ?? 0,
                            'light' => $weather->light ?? 0,
                            'rain' => $weather->rain ?? 0,
                            'rain_adc' => $weather->rain_adc ?? 0,
                            'wind' => $weather->wind ?? 0,
                            'wind_pulse' => $weather->wind_pulse ?? 0,
                            'humidity' => $weather->humidity ?? 0,
                            'temp_dht' => $weather->temp_dht ?? 0,
                            'rssi' => $weather->rssi ?? 0,
                            'snr' => $weather->snr ?? 0,
                            'signal_quality' => $this->getSignalQuality($weather->rssi ?? 0, $weather->snr ?? 0),
                        ];
                    }),
                    'sensor_node_data' => $log->sensorNodeData->map(function ($node) {
                        return [
                            'id' => $node->id,
                            'sesi_id_getdata' => $node->sesi_id_getdata,
                            'node_id' => $node->node_id,
                            'rssi_dbm' => $node->rssi_dbm ?? 0,
                            'snr_db' => $node->snr_db ?? 0,
                            'voltage_v' => $node->voltage_v ?? 0,
                            'current_ma' => $node->current_ma ?? 0,
                            'power_mw' => $node->power_mw ?? 0,
                            'temp_c' => $node->temp_c ?? 0,
                            'soil_pct' => $node->soil_pct ?? 0,
                            'soil_adc' => $node->soil_adc ?? 0,
                            'ts_counter' => $node->ts_counter ?? 0,
                            'received_at' => $node->received_at ?? $node->created_at,
                        ];
                    }),
                    'total_records' => [
                        'sensor_weather_data' => $log->sensorWeatherData->count(),
                        'sensor_node_data' => $receivedNodes,
                    ],
                    'data_completeness' => [
                        'expected_nodes' => $expectedNodes,
                        'received_nodes' => $receivedNodes,
                        'completeness_percentage' => $completenessPercentage . '%',
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data log not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function index()
    {
        try {
            $logs = GetDataLog::orderBy('waktu_mulai', 'desc')->get();
            return response()->json([
                'success' => true,
                'data' => $logs
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
            $log = GetDataLog::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $log
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
                'waktu_mulai' => 'required|date',
                'waktu_selesai' => 'nullable|date',
                'node_sukses' => 'nullable|integer',
                'node_gagal' => 'nullable|integer',
            ]);

            $log = GetDataLog::create($data);
            return response()->json([
                'success' => true,
                'data' => $log
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function storeBulkSensorData(Request $request)
    {
        try {
            // Validasi request
            $validated = $request->validate([
                'sesi_id_getdata' => 'required|integer',
                'waktu_mulai' => 'required|date',
                'waktu_selesai' => 'nullable|date',

                // Weather data (single object)
                'sensor_weather_data' => 'required|array',
                'sensor_weather_data.node_id' => 'required|integer',
                'sensor_weather_data.voltage' => 'nullable|numeric',
                'sensor_weather_data.current' => 'nullable|numeric',
                'sensor_weather_data.power' => 'nullable|numeric',
                'sensor_weather_data.light' => 'nullable|numeric',
                'sensor_weather_data.rain' => 'nullable|numeric',
                'sensor_weather_data.rain_adc' => 'nullable|integer',
                'sensor_weather_data.wind' => 'nullable|numeric',
                'sensor_weather_data.wind_pulse' => 'nullable|integer',
                'sensor_weather_data.humidity' => 'nullable|numeric',
                'sensor_weather_data.temp_dht' => 'nullable|numeric',
                'sensor_weather_data.rssi' => 'nullable|numeric',
                'sensor_weather_data.snr' => 'nullable|numeric',

                // Node data (array of objects)
                'sensor_node_data' => 'required|array|min:1',
                'sensor_node_data.*.node_id' => 'required|integer',
                'sensor_node_data.*.rssi_dbm' => 'nullable|numeric',
                'sensor_node_data.*.snr_db' => 'nullable|numeric',
                'sensor_node_data.*.voltage_v' => 'nullable|numeric',
                'sensor_node_data.*.current_ma' => 'nullable|numeric',
                'sensor_node_data.*.power_mw' => 'nullable|numeric',
                'sensor_node_data.*.temp_c' => 'nullable|numeric',
                'sensor_node_data.*.soil_pct' => 'nullable|numeric',
                'sensor_node_data.*.soil_adc' => 'nullable|integer',
                'sensor_node_data.*.ts_counter' => 'nullable|integer',
                'sensor_node_data.*.received_at' => 'nullable|date',
            ]);

            // Mulai database transaction
            DB::beginTransaction();

            // Cek apakah GetDataLog dengan sesi_id sudah ada
            $getDataLog = GetDataLog::where('sesi_id_getdata', $validated['sesi_id_getdata'])->first();

            if (!$getDataLog) {
                // Buat GetDataLog baru jika belum ada
                $getDataLog = GetDataLog::create([
                    'sesi_id_getdata' => $validated['sesi_id_getdata'],
                    'waktu_mulai' => $validated['waktu_mulai'],
                    'waktu_selesai' => $validated['waktu_selesai'] ?? now(),
                    'node_sukses' => count($validated['sensor_node_data']),
                    'node_gagal' => 0,
                ]);
            } else {
                // Update jika sudah ada
                $getDataLog->update([
                    'waktu_selesai' => $validated['waktu_selesai'] ?? now(),
                    'node_sukses' => count($validated['sensor_node_data']),
                ]);
            }

            // Simpan Sensor Weather Data (single data)
            $weatherData = \App\Models\SensorWeatherData::create([
                'sesi_id_getdata' => $validated['sesi_id_getdata'],
                'node_id' => $validated['sensor_weather_data']['node_id'],
                'voltage' => $validated['sensor_weather_data']['voltage'] ?? null,
                'current' => $validated['sensor_weather_data']['current'] ?? null,
                'power' => $validated['sensor_weather_data']['power'] ?? null,
                'light' => $validated['sensor_weather_data']['light'] ?? null,
                'rain' => $validated['sensor_weather_data']['rain'] ?? null,
                'rain_adc' => $validated['sensor_weather_data']['rain_adc'] ?? null,
                'wind' => $validated['sensor_weather_data']['wind'] ?? null,
                'wind_pulse' => $validated['sensor_weather_data']['wind_pulse'] ?? null,
                'humidity' => $validated['sensor_weather_data']['humidity'] ?? null,
                'temp_dht' => $validated['sensor_weather_data']['temp_dht'] ?? null,
                'rssi' => $validated['sensor_weather_data']['rssi'] ?? null,
                'snr' => $validated['sensor_weather_data']['snr'] ?? null,
            ]);

            // Simpan Sensor Node Data (multiple data)
            $savedNodes = [];
            foreach ($validated['sensor_node_data'] as $nodeData) {
                $savedNode = \App\Models\SensorNodeData::create([
                    'sesi_id_getdata' => $validated['sesi_id_getdata'],
                    'node_id' => $nodeData['node_id'],
                    'rssi_dbm' => $nodeData['rssi_dbm'] ?? null,
                    'snr_db' => $nodeData['snr_db'] ?? null,
                    'voltage_v' => $nodeData['voltage_v'] ?? null,
                    'current_ma' => $nodeData['current_ma'] ?? null,
                    'power_mw' => $nodeData['power_mw'] ?? null,
                    'temp_c' => $nodeData['temp_c'] ?? null,
                    'soil_pct' => $nodeData['soil_pct'] ?? null,
                    'soil_adc' => $nodeData['soil_adc'] ?? null,
                    'ts_counter' => $nodeData['ts_counter'] ?? null,
                    'received_at' => $nodeData['received_at'] ?? now(),
                ]);
                $savedNodes[] = $savedNode;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sensor data stored successfully',
                'data' => [
                    'getdata_log' => [
                        'id' => $getDataLog->id,
                        'sesi_id_getdata' => $getDataLog->sesi_id_getdata,
                        'waktu_mulai' => $getDataLog->waktu_mulai,
                        'waktu_selesai' => $getDataLog->waktu_selesai,
                        'node_sukses' => $getDataLog->node_sukses,
                        'node_gagal' => $getDataLog->node_gagal,
                    ],
                    'sensor_weather_data' => [
                        'id' => $weatherData->id,
                        'sesi_id_getdata' => $weatherData->sesi_id_getdata,
                        'node_id' => $weatherData->node_id,
                        'voltage' => $weatherData->voltage,
                        'current' => $weatherData->current,
                        'power' => $weatherData->power,
                        'light' => $weatherData->light,
                        'rain' => $weatherData->rain,
                        'rain_adc' => $weatherData->rain_adc,
                        'wind' => $weatherData->wind,
                        'wind_pulse' => $weatherData->wind_pulse,
                        'humidity' => $weatherData->humidity,
                        'temp_dht' => $weatherData->temp_dht,
                        'rssi' => $weatherData->rssi,
                        'snr' => $weatherData->snr,
                        'signal_quality' => $this->getSignalQuality($weatherData->rssi ?? 0, $weatherData->snr ?? 0),
                    ],
                    'sensor_node_data' => collect($savedNodes)->map(function ($node) {
                        return [
                            'id' => $node->id,
                            'sesi_id_getdata' => $node->sesi_id_getdata,
                            'node_id' => $node->node_id,
                            'rssi_dbm' => $node->rssi_dbm,
                            'snr_db' => $node->snr_db,
                            'voltage_v' => $node->voltage_v,
                            'current_ma' => $node->current_ma,
                            'power_mw' => $node->power_mw,
                            'temp_c' => $node->temp_c,
                            'soil_pct' => $node->soil_pct,
                            'soil_adc' => $node->soil_adc,
                            'ts_counter' => $node->ts_counter,
                            'received_at' => $node->received_at,
                        ];
                    }),
                    'summary' => [
                        'weather_data_saved' => 1,
                        'node_data_saved' => count($savedNodes),
                    ]
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to store sensor data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $log = GetDataLog::findOrFail($id);

            $data = $request->validate([
                'waktu_selesai' => 'nullable|date',
                'node_sukses' => 'nullable|integer',
                'node_gagal' => 'nullable|integer',
            ]);

            $log->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Data log updated successfully',
                'data' => $log
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
            $log = GetDataLog::findOrFail($id);

            // Hapus data terkait terlebih dahulu
            $log->sensorWeatherData()->delete();
            $log->sensorNodeData()->delete();

            // Hapus log
            $log->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data log deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
