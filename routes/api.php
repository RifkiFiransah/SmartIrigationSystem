<?php

use App\Http\Controllers\Api\SensorDataController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DataManagementController;
use App\Http\Controllers\Api\WaterStorageController;
use App\Http\Controllers\Api\DataTransferController;
use App\Http\Controllers\Api\IrrigationController;
use App\Http\Controllers\Api\DeviceUsageController;
use App\Http\Controllers\BMKGForecastController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::prefix('devices/{device}')->group(function(){
    Route::get('/irrigation/sessions', [DeviceUsageController::class, 'sessions']);
    Route::get('/usage-history', [DeviceUsageController::class, 'usageHistory']);
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
Route::get('/health', function(){
    return response()->json(['ok'=>true,'time'=>now()->toDateTimeString()]);
});

// ===== BMKG FORECAST PROXY =====
Route::get('/bmkg/forecast', [BMKGForecastController::class, 'index']);
