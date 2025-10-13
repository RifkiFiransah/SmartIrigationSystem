<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorNodeData;
use Illuminate\Http\Request;

class SensorNodeDataController extends Controller
{
    public function index()
    {
        try {
            $data = SensorNodeData::orderBy('received_at', 'desc')->get();
            return response()->json([
                'success' => true,
                'data' => $data
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
            $record = SensorNodeData::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $record
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
                'node_id' => 'required|integer',
                'rssi_dbm' => 'nullable|numeric',
                'snr_db' => 'nullable|numeric',
                'voltage_v' => 'nullable|numeric',
                'current_ma' => 'nullable|numeric',
                'power_mw' => 'nullable|numeric',
                'temp_c' => 'nullable|numeric',
                'soil_pct' => 'nullable|numeric',
                'soil_adc' => 'nullable|integer',
                'ts_counter' => 'nullable|integer',
                'received_at' => 'nullable|date',
            ]);

            $rec = SensorNodeData::create($data);
            return response()->json([
                'success' => true,
                'data' => $rec
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $rec = SensorNodeData::findOrFail($id);
            $data = $request->validate([
                'sesi_id_getdata' => 'sometimes|integer',
                'node_id' => 'sometimes|integer',
                'rssi_dbm' => 'nullable|numeric',
                'snr_db' => 'nullable|numeric',
                'voltage_v' => 'nullable|numeric',
                'current_ma' => 'nullable|numeric',
                'power_mw' => 'nullable|numeric',
                'temp_c' => 'nullable|numeric',
                'soil_pct' => 'nullable|numeric',
                'soil_adc' => 'nullable|integer',
                'ts_counter' => 'nullable|integer',
                'received_at' => 'nullable|date',
            ]);

            $rec->update($data);
            return response()->json([
                'success' => true,
                'data' => $rec
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
            $rec = SensorNodeData::findOrFail($id);
            $rec->delete();
            return response()->json([
                'success' => true,
                'message' => 'Data deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}