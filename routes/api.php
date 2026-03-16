<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\LocationPingController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public endpoints (no auth required)
Route::post('/login', [AuthController::class, 'apiLogin']);
Route::post('/devices/register-with-license', [DeviceController::class, 'registerWithLicense']);
Route::post('/devices/get-token', [DeviceController::class, 'getToken']);
Route::get('/devices/avatar-icons', [DeviceController::class, 'getAvatarIcons']);
Route::post('/pings', [LocationPingController::class, 'store']);
Route::get('/devices/{deviceId}/check-updates', [DeviceController::class, 'checkUpdates']);
Route::get('/public/settings', [App\Http\Controllers\AdminController::class, 'getPublicSettings']);

// OTP endpoints (no auth required for registration flow)
Route::prefix('otp')->group(function () {
    Route::post('/send-phone', [App\Http\Controllers\OTPController::class, 'sendPhone']);
    Route::post('/send-email', [App\Http\Controllers\OTPController::class, 'sendEmail']);
    Route::post('/verify', [App\Http\Controllers\OTPController::class, 'verify']);
    Route::post('/resend', [App\Http\Controllers\OTPController::class, 'resend']);
});

// Device management endpoints (require auth and user status check)
Route::middleware(['auth:sanctum', 'check.user.status'])->group(function () {
    Route::post('/devices/register', [DeviceController::class, 'register']);
    Route::get('/devices', [DeviceController::class, 'index']);
    Route::post('/devices/{deviceId}/uninstall', [App\Http\Controllers\UserController::class, 'uninstallDevice']);
});

// Protected endpoints (require web auth and user status check for dashboard)
Route::middleware(['web', 'check.user.status'])->group(function () {
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

// Admin endpoints (require web auth and admin privileges)
Route::middleware(['web', 'admin'])->prefix('admin')->group(function () {
    Route::get('/settings', [App\Http\Controllers\AdminController::class, 'getSettings']);
    Route::put('/settings', [App\Http\Controllers\AdminController::class, 'updateSettings']);
    Route::post('/test-email', [App\Http\Controllers\AdminController::class, 'testEmail']);
    Route::post('/test-sms', [App\Http\Controllers\AdminController::class, 'testSMS']);
    Route::get('/users', [App\Http\Controllers\AdminController::class, 'getUsers']);
    Route::get('/devices', [App\Http\Controllers\AdminController::class, 'getAllDevices']);
    
    // User management endpoints
    Route::post('/users/{userId}/generate-license', [App\Http\Controllers\AdminController::class, 'generateLicenseKey']);
    Route::post('/users/{userId}/approve', [App\Http\Controllers\AdminController::class, 'approveUser']);
    Route::post('/users/{userId}/unapprove', [App\Http\Controllers\AdminController::class, 'unapproveUser']);
    Route::post('/users/{userId}/suspend', [App\Http\Controllers\AdminController::class, 'suspendUser']);
    Route::post('/users/{userId}/unsuspend', [App\Http\Controllers\AdminController::class, 'unsuspendUser']);
    Route::delete('/users/{userId}', [App\Http\Controllers\AdminController::class, 'deleteUser']);
    
    // Device cleanup endpoint
    Route::post('/devices/cleanup-inactive', [DeviceController::class, 'cleanupInactive']);
});
