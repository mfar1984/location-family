<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\LocationPing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationPingController extends Controller
{
    /**
     * Receive and store device ping data
     * 
     * POST /api/pings
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'licenseKey' => 'required|string',
            'deviceId' => 'required|string',
            'name' => 'required|string|min:1',
            'latitude' => 'required|numeric|min:-90|max:90',
            'longitude' => 'required|numeric|min:-180|max:180',
            'accuracy' => 'nullable|numeric|min:0',
            'batteryLevel' => 'required|integer|min:0|max:100',
            'signalStrength' => 'nullable|integer',
            'microphoneStatus' => 'nullable|boolean',
            'cameraStatus' => 'nullable|boolean',
            'recordingStatus' => 'nullable|boolean',
            'timestamp' => 'required|integer',
            'deviceModel' => 'nullable|string',
            'deviceBrand' => 'nullable|string',
            'osVersion' => 'nullable|string',
            'appVersion' => 'nullable|string',
            'serialNumber' => 'nullable|string',
            'phoneNumber' => 'nullable|string',
        ]);

        DB::beginTransaction();

        // Find the device by device_id
        $device = Device::where('device_id', $request->deviceId)->first();

        if (!$device) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Device not found',
                'code' => 'DEVICE_NOT_FOUND',
                'timestamp' => now()->timestamp * 1000,
            ], 404);
        }

        // Validate license key matches the device's user
        if ($device->user->license_key !== $request->licenseKey) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Invalid license key',
                'code' => 'INVALID_LICENSE_KEY',
                'timestamp' => now()->timestamp * 1000,
            ], 403);
        }

        // Check if user is approved and not suspended
        if (!$device->user->approved) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'User account not approved',
                'code' => 'USER_NOT_APPROVED',
                'timestamp' => now()->timestamp * 1000,
            ], 403);
        }

        if ($device->user->suspended) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'User account suspended',
                'code' => 'USER_SUSPENDED',
                'timestamp' => now()->timestamp * 1000,
            ], 403);
        }

        // Create the location ping
        $ping = LocationPing::create([
            'device_id' => $device->id,
            'name' => $request->name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
            'battery_level' => $request->batteryLevel,
            'signal_strength' => $request->signalStrength,
            'microphone_status' => $request->microphoneStatus,
            'camera_status' => $request->cameraStatus,
            'recording_status' => $request->recordingStatus,
            'ping_timestamp' => $request->timestamp,
            'received_at' => now(),
            'ip_address' => $request->ip(),
            'device_model' => $request->deviceModel,
            'device_brand' => $request->deviceBrand,
            'os_version' => $request->osVersion,
            'app_version' => $request->appVersion,
            'serial_number' => $request->serialNumber,
            'phone_number' => $request->phoneNumber,
        ]);

        // Update device last_seen timestamp
        $device->update([
            'last_seen' => now(),
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'ping_id' => $ping->id,
        ], 201);
    }
}
