<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IrrigationValve;
use App\Models\IrrigationValveSchedule;
use App\Models\SensorData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NodeValveController extends Controller
{
    public function listSchedules(string $nodeUid): JsonResponse
    {
        $schedules = IrrigationValveSchedule::where('node_uid', $nodeUid)->orderByDesc('created_at')->get();
        return response()->json(['success' => true, 'data' => $schedules]);
    }

    public function status(string $nodeUid): JsonResponse
    {
        $valve = IrrigationValve::where('node_uid', $nodeUid)->first();
        if (!$valve) {
            return response()->json(['success' => false, 'message' => 'Valve not found'], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $valve,
        ]);
    }

    public function open(Request $request, string $nodeUid): JsonResponse
    {
        $valve = IrrigationValve::firstOrCreate(['node_uid' => $nodeUid]);
        $max = $request->integer('max_duration_minutes');
        $valve->open($max > 0 ? $max : null);
        return response()->json(['success' => true, 'message' => 'Valve opened', 'data' => $valve]);
    }

    public function close(string $nodeUid): JsonResponse
    {
        $valve = IrrigationValve::where('node_uid', $nodeUid)->first();
        if (!$valve) {
            return response()->json(['success' => false, 'message' => 'Valve not found'], 404);
        }
        $valve->close();
        return response()->json(['success' => true, 'message' => 'Valve closed', 'data' => $valve]);
    }

    public function setMode(Request $request, string $nodeUid): JsonResponse
    {
        $request->validate(['mode' => 'required|in:auto,manual']);
        $valve = IrrigationValve::firstOrCreate(['node_uid' => $nodeUid]);
        $valve->mode = $request->string('mode');
        $valve->save();
        return response()->json(['success' => true, 'message' => 'Mode updated', 'data' => $valve]);
    }

    public function evaluate(string $nodeUid): JsonResponse
    {
        $valve = IrrigationValve::where('node_uid', $nodeUid)->first();
        if (!$valve) {
            return response()->json(['success' => false, 'message' => 'Valve not found'], 404);
        }
        if ($valve->mode !== 'auto') {
            return response()->json(['success' => false, 'message' => 'Valve not in auto mode', 'data' => $valve], 400);
        }

        // Simple: just record evaluation time; no rules or automatic decisions here
        $valve->last_evaluated_at = now();
        $valve->save();

        $latest = $valve->device_id ? SensorData::where('device_id', $valve->device_id)->latest('recorded_at')->first() : null;

        return response()->json([
            'success' => true,
            'message' => 'Evaluated (no-op)',
            'data' => [
                'action' => 'no-op',
                'valve' => $valve,
                'sensor' => $latest ? [
                    'soil_moisture' => $latest->soil_moisture,
                    'device_id' => $valve->device_id,
                ] : null,
            ],
        ]);
    }
}
