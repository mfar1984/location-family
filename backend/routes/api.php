<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\LocationPingController;
use App\Http\Controllers\LocationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public endpoints (no auth required)
Route::get('/devices/avatar-icons', [DeviceController::class, 'getAvatarIcons']);
Route::post('/pings', [LocationPingController::class, 'store']);
Route::get('/devices/{deviceId}/check-updates', [DeviceController::class, 'checkUpdates']);

// Protected endpoints (require web auth for dashboard)
Route::middleware('web')->group(function () {
    Route::get('/locations', [LocationController::class, 'index']);
    Route::get('/locations/{deviceId}', [LocationController::class, 'show']);
    Route::post('/devices/{deviceId}/update', [DeviceController::class, 'triggerUpdate']);
    
    // User management endpoints
    Route::get('/user/devices', [App\Http\Controllers\UserController::class, 'getDevices']);
    Route::get('/user/profile-data', [App\Http\Controllers\UserController::class, 'getProfileData']);
    Route::post('/devices/{deviceId}/generate-code', [App\Http\Controllers\UserController::class, 'generateVerificationCode']);
    Route::delete('/devices/{deviceId}', [App\Http\Controllers\UserController::class, 'deleteDevice']);
    Route::delete('/devices/{deviceId}/with-code', [App\Http\Controllers\UserController::class, 'deleteDeviceWithCode']);
    Route::put('/devices/{deviceId}/name', [App\Http\Controllers\UserController::class, 'updateDeviceName']);
    Route::put('/user/profile', [App\Http\Controllers\UserController::class, 'updateProfile']);
    Route::put('/user/avatar', [App\Http\Controllers\UserController::class, 'updateAvatar']);
    Route::post('/user/change-password', [App\Http\Controllers\UserController::class, 'changePassword']);
});

// Device management endpoints (require auth)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/devices/register', [DeviceController::class, 'register']);
    Route::get('/devices', [DeviceController::class, 'index']);
});
