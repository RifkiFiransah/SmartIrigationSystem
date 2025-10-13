<?php

use App\Http\Controllers\Api\SensorDataController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DataManagementController;
use App\Http\Controllers\Api\WaterStorageController;
use App\Http\Controllers\Api\DataTransferController;
use App\Http\Controllers\Api\IrrigationController;
use App\Http\Controllers\Api\DeviceUsageController;
use App\Http\Controllers\Api\GetDataLogController;
use App\Http\Controllers\Api\NodeController;
use App\Http\Controllers\Api\SensorNodeDataController;
use App\Http\Controllers\Api\SensorWeatherDataController;
use App\Http\Controllers\Api\WeatherController;
use App\Http\Controllers\BMKGForecastController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Device;
use Carbon\Carbon;

Route::prefix('v1')->group(function () {
    Route::get('/nodes', [NodeController::class, 'index']);
    Route::get('/nodes/{id}', [NodeController::class, 'show']);
    Route::post('/nodes', [NodeController::class, 'store']);
    Route::put('/nodes/{id}', [NodeController::class, 'update']);
    Route::delete('/nodes/{id}', [NodeController::class, 'destroy']);
});

Route::prefix('v1')->group(function () {
    // Get all combined data
    Route::get('/sensor-datas', [GetDataLogController::class, 'getCombinedData']);
    
    // Get combined data by ID
    Route::get('/sensor-datas/{id}', [GetDataLogController::class, 'getCombinedDatabyIdGetDataLog']);
    
    // Store bulk sensor data (1 weather + multiple nodes)
    Route::post('/sensor-datas/bulk', [GetDataLogController::class, 'storeBulkSensorData']);
});

// CRUD GetDataLog
Route::get('/v1/getDataLogs', [GetDataLogController::class, 'index']);
Route::get('/v1/getDataLogs/{id}', [GetDataLogController::class, 'show']);
Route::post('/v1/getDataLogs', [GetDataLogController::class, 'store']);
Route::put('/v1/getDataLogs/{id}', [GetDataLogController::class, 'update']);
Route::delete('/v1/getDataLogs/{id}', [GetDataLogController::class, 'destroy']);

// ===== SENSOR NODE DATA ROUTES =====
Route::prefix('v1')->group(function () {
    Route::get('/sensorNodeData', [SensorNodeDataController::class, 'index']);
    Route::get('/sensorNodeData/{id}', [SensorNodeDataController::class, 'show']);
    Route::post('/sensorNodeData', [SensorNodeDataController::class, 'store']);
    Route::put('/sensorNodeData/{id}', [SensorNodeDataController::class, 'update']);
    Route::delete('/sensorNodeData/{id}', [SensorNodeDataController::class, 'destroy']);
});

// Route::get('/v1/getDataLogs', [GetDataLogController::class, 'index']);
// Route::get('/v1/getDataLogs/{id}', [GetDataLogController::class, 'show']);
// Route::post('/v1/getDataLogs', [GetDataLogController::class, 'store']);
// Route::put('/v1/getDataLogs/{id}', [GetDataLogController::class, 'update']);
// Route::delete('/v1/getDataLogs/{id}', [GetDataLogController::class, 'destroy']);

Route::get('/v1/sensorWeatherData', [SensorWeatherDataController::class, 'index']);
Route::get('/v1/sensorWeatherData/{id}', [SensorWeatherDataController::class, 'show']);
Route::post('/v1/sensorWeatherData', [SensorWeatherDataController::class, 'store']);
Route::put('/v1/sensorWeatherData/{id}', [SensorWeatherDataController::class, 'update']);
Route::delete('/v1/sensorWeatherData/{id}', [SensorWeatherDataController::class, 'destroy']);

// ===== AUTH ROUTES =====
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/auth/profile', [AuthController::class, 'profile'])->middleware('auth:sanctum');

// ===== PUBLIC SENSOR DATA ROUTES (FOR FRONTEND) =====
// Didefinisikan SEBELUM apiResource agar tidak tertangkap oleh route parameter {sensor_reading}
Route::get('/sensor-readings/latest', [SensorDataController::class, 'latest']);
Route::get('/sensor-readings/latest-per-device', [SensorDataController::class, 'latestPerDevice']);
Route::get('/sensor-readings/hourly', [SensorDataController::class, 'hourlyData']);
Route::get('/sensor-readings/daily', [SensorDataController::class, 'dailyData']);

// ===== SENSOR DATA ROUTES (DENGAN AUTH) =====
Route::apiResource('/sensor-readings', SensorDataController::class)->middleware('auth:sanctum');

// ===== DATA MANAGEMENT ROUTES =====
Route::prefix('data')->middleware('auth:sanctum')->group(function () {
    Route::delete('/clear', [DataManagementController::class, 'clearSensorData']);
    Route::delete('/clear-old', [DataManagementController::class, 'clearOldData']);
    Route::get('/stats', [DataManagementController::class, 'getDataStats']);
});

// ===== WATER STORAGE ROUTES =====
Route::prefix('water-storage')->group(function () {
    // Public routes for reading data
    Route::get('/', [WaterStorageController::class, 'index']);
    Route::get('/tank/{tankName}', [WaterStorageController::class, 'getByTankName']);
    Route::get('/daily-usage', [WaterStorageController::class, 'dailyUsage']);
    Route::get('/hourly-usage', [WaterStorageController::class, 'hourlyUsage']);

    // Protected route for updating volume (for IoT devices)
    Route::post('/update-volume', [WaterStorageController::class, 'updateVolume'])->middleware('auth:sanctum');
});

// ===== IRRIGATION ROUTES =====
Route::prefix('irrigation')->group(function () {
    Route::get('/today-plan', [IrrigationController::class, 'todayPlan']);
});

// ===== ZONE MANAGEMENT ROUTES =====
Route::prefix('zones')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\ZoneController::class, 'getZonesSummary']);
    Route::get('/{zoneName}', [App\Http\Controllers\Api\ZoneController::class, 'getZoneDetails']);
});

// ===== IRRIGATION LINES ROUTES =====
Route::prefix('irrigation-lines')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\IrrigationLineController::class, 'getIrrigationLinesSummary']);
    Route::get('/area/{areaName}', [App\Http\Controllers\Api\IrrigationLineController::class, 'getAreaIrrigationLines']);
    Route::get('/analytics/efficiency', [App\Http\Controllers\Api\IrrigationLineController::class, 'getLineEfficiencyAnalytics']);
    Route::get('/line/{lineId}', [App\Http\Controllers\Api\IrrigationLineController::class, 'getLineDetails']);
});

// ===== NODE VALVE ROUTES (Per-node open/close control) =====
Route::prefix('nodes/{nodeUid}/valve')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\NodeValveController::class, 'status']);
    Route::post('/open', [App\Http\Controllers\Api\NodeValveController::class, 'open']);
    Route::post('/close', [App\Http\Controllers\Api\NodeValveController::class, 'close']);
    Route::post('/mode', [App\Http\Controllers\Api\NodeValveController::class, 'setMode']);
    Route::post('/evaluate', [App\Http\Controllers\Api\NodeValveController::class, 'evaluate']);
    Route::get('/schedules', [App\Http\Controllers\Api\NodeValveController::class, 'listSchedules']);
});

// legacy irrigation control routes removed

// ===== HARDWARE DATA TRANSFER ROUTES (FOR MQTT/IoT DEVICES) =====
Route::prefix('transfer')->group(function () {
    // Public endpoints untuk hardware tanpa auth (gunakan API key jika diperlukan)
    Route::post('/sensor-data', [DataTransferController::class, 'storeSensorData']);
    Route::post('/water-level', [DataTransferController::class, 'storeWaterLevel']);
    Route::post('/simple-water-level', [DataTransferController::class, 'simpleWaterLevel']);
    Route::post('/heartbeat', [DataTransferController::class, 'heartbeat']);

    // GET endpoints untuk hardware mendapatkan konfigurasi
    Route::get('/device-config/{device_id}', [DataTransferController::class, 'getDeviceConfig']);
    Route::get('/system-status', [DataTransferController::class, 'getSystemStatus']);
});

// ===== IRRIGATION PLAN (SIMPLE) =====
Route::prefix('irrigation')->group(function () {
    Route::get('/today-plan', [\App\Http\Controllers\Api\IrrigationPlanController::class, 'today']);
    Route::post('/session-report', [\App\Http\Controllers\Api\IrrigationPlanController::class, 'report']);
});

// ===== DEVICE USAGE (Per-device sessions & history) =====
Route::prefix('devices/{device}')->group(function () {
    Route::get('/irrigation/sessions', [DeviceUsageController::class, 'sessions']);
    Route::get('/usage-history', [DeviceUsageController::class, 'usageHistory']);
});

// ===== DEVICE VALVE & CONNECTION SIMPLE ENDPOINTS =====
Route::prefix('devices/{device}')->group(function () {
    Route::get('/valve', function (Device $device) {
        return response()->json([
            'device_id' => $device->id,
            'valve_state' => $device->valve_state,
            'valve_state_changed_at' => $device->valve_state_changed_at,
        ]);
    });
    Route::post('/valve', function (\Illuminate\Http\Request $request, Device $device) {
        $request->validate(['valve_state' => 'required|in:open,closed']);
        $device->toggleValve($request->valve_state);
        return response()->json(['ok' => true]);
    });
    Route::get('/connection', function (Device $device) {
        return response()->json([
            'device_id' => $device->id,
            'connection_state' => $device->connection_state,
            'source' => $device->connection_state_source,
            'last_seen_at' => $device->last_seen_at,
        ]);
    });
    Route::post('/connection', function (\Illuminate\Http\Request $request, Device $device) {
        $request->validate(['state' => 'required|in:online,offline']);
        $device->setConnectionState($request->state, 'manual');
        return response()->json(['ok' => true]);
    });
});

// ===== USER ROUTE =====
Route::get('/user', function (Request $request) {
    return $request->user();
    return response()->json([
        'success' => true,
        'message' => 'User profile retrieved successfully',
        'data' => [
            'user' => $request->user(),
        ]
    ]);
})->middleware('auth:sanctum');

// ===== SIMPLE HEALTH CHECK (PUBLIC) =====
Route::get('/health', function () {
    return response()->json(['ok' => true, 'time' => Carbon::now()->format('Y-m-d H:i:s')]);
});

// ===== BMKG FORECAST PROXY =====
Route::get('/bmkg/forecast', [BMKGForecastController::class, 'index']);

// ===== EXTERNAL WEATHER (BMKG/Open-Meteo proxy) =====
Route::get('/weather/external', [WeatherController::class, 'external']);
Route::get('/weather/hourly', [WeatherController::class, 'hourly']);
