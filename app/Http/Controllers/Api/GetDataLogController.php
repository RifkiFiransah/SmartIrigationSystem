<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GetDataLog;
use Illuminate\Http\Request;

class GetDataLogController extends Controller
{
    public function getCombinedData()
    {
        try {
            $logs = GetDataLog::with(['sensorNodeData', 'sensorWeatherData'])->orderBy('waktu_mulai', 'desc')->get();
            $logs_node = $logs->sensorNodeData()->with('node')->get();
            return response()->json([
                'success' => true,
                'data_node' => $logs_node,
                'data' => $logs,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
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