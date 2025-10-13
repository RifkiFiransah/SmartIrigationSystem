<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GetDataLog;
use Illuminate\Http\Request;

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
            $log = GetDataLog::with(['sensorNodeData', 'sensorWeatherData'])->where('id', $id)->firstOrFail();
            return response()->json([
                'success' => true,
                'data' => $log,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
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
}
