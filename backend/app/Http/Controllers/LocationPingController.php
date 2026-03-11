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
