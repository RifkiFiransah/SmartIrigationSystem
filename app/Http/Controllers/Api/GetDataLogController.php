<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GetDataLog;
use Illuminate\Http\Request;

class GetDataLogController extends Controller
{
    public function index()
    {
        return response()->json(GetDataLog::latest('waktu_mulai')->get());
    }

    public function show($id)
    {
        $log = GetDataLog::findOrFail($id);
        return response()->json($log);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id' => 'sometimes|integer',
            'sesi_id_getdata' => 'required|integer',
            'waktu_mulai' => 'required|date',
            'waktu_selesai' => 'nullable|date',
            'node_sukses' => 'nullable|integer',
            'node_gagal' => 'nullable|integer',
        ]);

        $log = GetDataLog::create($data);
        return response()->json($log, 201);
    }

    public function update(Request $request, $id)
    {
        $log = GetDataLog::findOrFail($id);
        $data = $request->validate([
            'sesi_id_getdata' => 'sometimes|integer',
            'waktu_mulai' => 'sometimes|date',
            'waktu_selesai' => 'nullable|date',
            'node_sukses' => 'nullable|integer',
            'node_gagal' => 'nullable|integer',
        ]);

        $log->update($data);
        return response()->json($log);
    }

    public function destroy($id)
    {
        $log = GetDataLog::findOrFail($id);
        $log->delete();
        return response()->json(null, 204);
    }
}
