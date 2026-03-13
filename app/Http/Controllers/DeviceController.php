<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    /**
     * Register a new device with name and device_id
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:1|max:255',
            'device_id' => 'required|string|unique:devices,device_id',
            'avatar_type' => 'nullable|in:icon,upload',
            'avatar_value' => 'nullable|string',
            'avatar_file' => 'nullable|image|max:2048', // 2MB max
        ]);

        // Get authenticated user (web or sanctum)
        $user = null;
        if (auth()->guard('web')->check()) {
            $user = auth()->guard('web')->user();
        } elseif (auth()->guard('sanctum')->check()) {
            $user = auth()->guard('sanctum')->user();
        }
        
        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized',
                'code' => 'UNAUTHORIZED',
            ], 401);
        }

        // Check device limit (max 10 devices per user)
        $deviceCount = Device::where('user_id', $user->id)->count();
        if ($deviceCount >= 10) {
            return response()->json([
                'error' => true,
                'message' => 'Device limit reached. Maximum 10 devices per account.',
                'code' => 'DEVICE_LIMIT_REACHED',
                'current_count' => $deviceCount,
                'max_limit' => 10,
            ], 403);
        }

        // Handle avatar upload
        $avatarType = $request->avatar_type ?? 'icon';
        $avatarValue = $request->avatar_value ?? 'person'; // default icon
        
        if ($request->hasFile('avatar_file')) {
            $path = $request->file('avatar_file')->store('avatars', 'public');
            $avatarType = 'upload';
            $avatarValue = $path;
        }

        $device = Device::create([
            'device_id' => $request->device_id,
            'name' => $request->name,
            'user_id' => $user->id,
            'avatar_type' => $avatarType,
            'avatar_value' => $avatarValue,
            'registered_at' => now(),
            'is_active' => true,
        ]);

        return response()->json([
            'deviceId' => $device->device_id,
            'name' => $device->name,
            'registered' => true,
        ], 201);
    }

    /**
     * List all registered devices
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $devices = Device::where('is_active', true)
            ->orderBy('registered_at', 'desc')
            ->get(['id', 'device_id', 'name', 'registered_at', 'last_seen', 'is_active']);

        return response()->json([
            'devices' => $devices,
        ], 200);
    }

    /**
     * Trigger manual update for a specific device
     * 
     * @param string $deviceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function triggerUpdate($deviceId)
    {
        // Find the device by device_id
        $device = Device::where('device_id', $deviceId)->first();

        if (!$device) {
            return response()->json([
                'error' => true,
                'message' => 'Device not found',
                'code' => 'DEVICE_NOT_FOUND',
                'timestamp' => now()->timestamp * 1000,
            ], 404);
        }

        // Store update request in cache with 5 minute expiration
        // The device will poll this endpoint to check for pending updates
        $cacheKey = "manual_update:{$deviceId}";
        \Cache::put($cacheKey, [
            'device_id' => $deviceId,
            'requested_at' => now()->timestamp * 1000,
            'status' => 'pending',
        ], now()->addMinutes(5));

        return response()->json([
            'success' => true,
            'message' => 'Manual update request sent',
            'deviceId' => $deviceId,
        ], 200);
    }

    /**
     * Check for pending update requests for a device
     *
     * @param string $deviceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkUpdates($deviceId)
    {
        // Find the device by device_id
        $device = Device::where('device_id', $deviceId)->first();

        if (!$device) {
            return response()->json([
                'error' => true,
                'message' => 'Device not found',
                'code' => 'DEVICE_NOT_FOUND',
                'timestamp' => now()->timestamp * 1000,
            ], 404);
        }

        // Check for pending update request in cache
        $cacheKey = "manual_update:{$deviceId}";
        $updateRequest = \Cache::get($cacheKey);

        if ($updateRequest && $updateRequest['status'] === 'pending') {
            // Clear the request after device retrieves it
            \Cache::forget($cacheKey);

            return response()->json([
                'updateRequested' => true,
                'requestedAt' => $updateRequest['requested_at'],
            ], 200);
        }

        // No pending update request
        return response()->json([
            'updateRequested' => false,
        ], 200);
    }

    /**
     * Get available avatar icons
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvatarIcons()
    {
        $iconsPath = public_path('avatars/icons.json');
        
        if (!file_exists($iconsPath)) {
            return response()->json([
                'error' => true,
                'message' => 'Icons file not found',
            ], 404);
        }
        
        $icons = json_decode(file_get_contents($iconsPath), true);
        
        return response()->json($icons, 200);
    }

    /**
     * Register a new device using license key (for Android app)
     * No login required - just license key + device name
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerWithLicense(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string|size:14', // Format: XXXX-XXXX-XXXX
            'name' => 'required|string|min:1|max:255',
            'avatar_type' => 'nullable|in:icon,upload',
            'avatar_value' => 'nullable|string',
        ]);

        // Find user by license key
        $user = \App\Models\User::where('license_key', $request->license_key)->first();

        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => 'Invalid license key',
                'code' => 'INVALID_LICENSE_KEY',
            ], 404);
        }

        // Check device limit (max 10 devices per user)
        $deviceCount = Device::where('user_id', $user->id)->count();
        if ($deviceCount >= 10) {
            return response()->json([
                'error' => true,
                'message' => 'Device limit reached for this license key. Maximum 10 devices.',
                'code' => 'DEVICE_LIMIT_REACHED',
                'current_count' => $deviceCount,
                'max_limit' => 10,
            ], 403);
        }

        // Generate unique device_id
        $deviceId = 'device-' . strtolower($request->name) . '-' . time() . rand(100, 999);
        
        // Check if device_id already exists (unlikely but possible)
        while (Device::where('device_id', $deviceId)->exists()) {
            $deviceId = 'device-' . strtolower($request->name) . '-' . time() . rand(100, 999);
        }

        // Handle avatar
        $avatarType = $request->avatar_type ?? 'icon';
        $avatarValue = $request->avatar_value ?? 'person'; // default icon

        $device = Device::create([
            'device_id' => $deviceId,
            'name' => $request->name,
            'user_id' => $user->id,
            'avatar_type' => $avatarType,
            'avatar_value' => $avatarValue,
            'registered_at' => now(),
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device registered successfully',
            'device' => [
                'device_id' => $device->device_id,
                'name' => $device->name,
                'avatar_type' => $device->avatar_type,
                'avatar_value' => $device->avatar_value,
                'registered_at' => $device->registered_at->toIso8601String(),
            ],
            'user' => [
                'name' => $user->name,
                'license_key' => $user->license_key,
                'devices_count' => $deviceCount + 1,
                'devices_limit' => 10,
            ],
        ], 201);
    }

    /**
     * Cleanup inactive devices (Admin only)
     * 
     * POST /api/admin/devices/cleanup-inactive
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cleanupInactive(Request $request)
    {
        // Check if user is admin
        $user = null;
        if (auth()->guard('web')->check()) {
            $user = auth()->guard('web')->user();
        } elseif (auth()->guard('sanctum')->check()) {
            $user = auth()->guard('sanctum')->user();
        }
        
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized. Admin access required.',
                'code' => 'UNAUTHORIZED',
            ], 403);
        }
        
        $request->validate([
            'days' => 'nullable|integer|min:1|max:365',
            'delete' => 'nullable|boolean',
        ]);
        
        $days = $request->input('days', 7);
        $shouldDelete = $request->input('delete', false);
        
        $cutoffDate = now()->subDays($days);
        
        // Get all active devices
        $activeDevices = Device::where('is_active', true)->get();
        
        $inactiveDevices = [];
        
        foreach ($activeDevices as $device) {
            if ($device->isInactive($days)) {
                $lastPingTime = $device->getLastPingTime();
                $inactiveDevices[] = [
                    'device_id' => $device->device_id,
                    'name' => $device->name,
                    'user_email' => $device->user->email ?? 'N/A',
                    'last_seen' => $lastPingTime ? $lastPingTime->toIso8601String() : null,
                ];
                
                if ($shouldDelete) {
                    // Delete location pings first
                    \App\Models\LocationPing::where('device_id', $device->id)->delete();
                    $device->delete();
                } else {
                    // Mark as inactive
                    $device->is_active = false;
                    $device->save();
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => $shouldDelete 
                ? 'Inactive devices deleted successfully' 
                : 'Inactive devices marked as inactive',
            'days' => $days,
            'action' => $shouldDelete ? 'deleted' : 'marked_inactive',
            'devices_processed' => count($inactiveDevices),
            'devices' => $inactiveDevices,
        ], 200);
    }
}
