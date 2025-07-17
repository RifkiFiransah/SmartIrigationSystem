<?php

use App\Http\Controllers\Api\SensorDataController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DataManagementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ===== AUTH ROUTES =====
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/auth/profile', [AuthController::class, 'profile'])->middleware('auth:sanctum');

// ===== SENSOR DATA ROUTES (DENGAN AUTH) =====
Route::apiResource('/sensor-readings', SensorDataController::class)->middleware('auth:sanctum');

// ===== DATA MANAGEMENT ROUTES =====
Route::prefix('data')->middleware('auth:sanctum')->group(function () {
    Route::delete('/clear', [DataManagementController::class, 'clearSensorData']);
    Route::delete('/clear-old', [DataManagementController::class, 'clearOldData']);
    Route::get('/stats', [DataManagementController::class, 'getDataStats']);
});

// ===== USER ROUTE =====
Route::get('/user', function (Request $request) {
    return $request->user();
    // return response()->json([
    //     'success' => true,
    //     'message' => 'User profile retrieved successfully',
    //     'data' => [
    //         'user' => $request->user(),
    //     ]
    // ]);
})->middleware('auth:sanctum');
