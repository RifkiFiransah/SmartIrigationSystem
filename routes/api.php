<?php

use App\Http\Controllers\Api\SensorDataController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DataManagementController;
use App\Http\Controllers\Api\WaterStorageController;
use App\Http\Controllers\Api\DataTransferController;
use App\Http\Controllers\Api\IrrigationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ===== AUTH ROUTES =====
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/auth/profile', [AuthController::class, 'profile'])->middleware('auth:sanctum');

// ===== SENSOR DATA ROUTES (DENGAN AUTH) =====
Route::apiResource('/sensor-readings', SensorDataController::class)->middleware('auth:sanctum');

// ===== PUBLIC SENSOR DATA ROUTES (FOR FRONTEND) =====
Route::get('/sensor-readings/latest', [SensorDataController::class, 'latest']);
Route::get('/sensor-readings/latest-per-device', [SensorDataController::class, 'latestPerDevice']);
Route::get('/sensor-readings/hourly', [SensorDataController::class, 'hourlyData']);
Route::get('/sensor-readings/daily', [SensorDataController::class, 'dailyData']);

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
    
    // Protected route for updating volume (for IoT devices)
    Route::post('/update-volume', [WaterStorageController::class, 'updateVolume'])->middleware('auth:sanctum');
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

// ===== IRRIGATION CONTROL ROUTES =====
Route::prefix('irrigation')->group(function () {
    // Public endpoints untuk reading (dashboard)
    Route::get('/controls', [IrrigationController::class, 'getControls']);
    Route::get('/status', [IrrigationController::class, 'getStatus']);
    Route::get('/logs', [IrrigationController::class, 'getLogs']);
    
    // Control endpoints (bisa dibuat public untuk hardware atau protected)
    Route::post('/start', [IrrigationController::class, 'startIrrigation']);
    Route::post('/stop', [IrrigationController::class, 'stopIrrigation']);
    Route::post('/toggle-mode', [IrrigationController::class, 'toggleMode']);
    
    // System endpoints
    Route::post('/run-scheduled', [IrrigationController::class, 'runScheduled']);
    Route::post('/emergency-stop', [IrrigationController::class, 'emergencyStop']);
});

// ===== HARDWARE DATA TRANSFER ROUTES (FOR MQTT/IoT DEVICES) =====
Route::prefix('transfer')->group(function () {
    // Public endpoints untuk hardware tanpa auth (gunakan API key jika diperlukan)
    Route::post('/sensor-data', [DataTransferController::class, 'storeSensorData']);
    Route::post('/water-level', [DataTransferController::class, 'storeWaterLevel']);
    Route::post('/heartbeat', [DataTransferController::class, 'heartbeat']);
    
    // GET endpoints untuk hardware mendapatkan konfigurasi
    Route::get('/device-config/{device_id}', [DataTransferController::class, 'getDeviceConfig']);
    Route::get('/system-status', [DataTransferController::class, 'getSystemStatus']);
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
