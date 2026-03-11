<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\LocationPing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    /**
     * Get all active device locations (last 5 minutes)
     * 
     * GET /api/locations
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $fiveMinutesAgo = now()->subMinutes(5);
        $twoMinutesAgo = now()->subMinutes(2);

        // Get authenticated user (web auth for dashboard)
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

        // Build query for latest pings - filter by user_id
        $query = LocationPing::select('location_pings.*')
            ->join(DB::raw('(SELECT device_id, MAX(ping_timestamp) as max_timestamp 
                             FROM location_pings 
                             WHERE received_at >= "' . $fiveMinutesAgo->toDateTimeString() . '"
                             GROUP BY device_id) as latest'), function($join) {
                $join->on('location_pings.device_id', '=', 'latest.device_id')
                     ->on('location_pings.ping_timestamp', '=', 'latest.max_timestamp');
            })
            ->with('device')
            ->whereHas('device', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });

        $latestPings = $query->get();

        // If no recent pings found, check if user has devices but no recent data
        if ($latestPings->count() === 0) {
            $userDeviceCount = Device::where('user_id', $user->id)
                                   ->where('is_active', true)
                                   ->count();
            
            if ($userDeviceCount > 0) {
                // User has devices but no recent pings - return message
                return response()->json([
                    'locations' => [],
                    'message' => 'No recent location data. Devices may be offline or need to send location updates.',
                    'deviceCount' => $userDeviceCount,
                ], 200);
            }
        }

        $locations = $latestPings->map(function ($ping) use ($twoMinutesAgo) {
            $isStale = $ping->received_at < $twoMinutesAgo;

            return [
                'deviceId' => $ping->device->device_id,
                'name' => $ping->name,
                'latitude' => (float) $ping->latitude,
                'longitude' => (float) $ping->longitude,
                'accuracy' => $ping->accuracy,
                'batteryLevel' => $ping->battery_level,
                'signalStrength' => $ping->signal_strength,
                'microphoneStatus' => $ping->microphone_status,
                'cameraStatus' => $ping->camera_status,
                'recordingStatus' => $ping->recording_status,
                'lastUpdate' => $ping->ping_timestamp,
                'isStale' => $isStale,
                'avatar' => $ping->device->avatar_data,
            ];
        });

        return response()->json([
            'locations' => $locations,
        ], 200);
    }

    /**
     * Get specific device location
     * 
     * GET /api/locations/{deviceId}
     * 
     * @param string $deviceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($deviceId)
    {
        $device = Device::where('device_id', $deviceId)->first();

        if (!$device) {
            return response()->json([
                'error' => true,
                'message' => 'Device not found',
                'code' => 'DEVICE_NOT_FOUND',
                'timestamp' => now()->timestamp * 1000,
            ], 404);
        }

        // Get the latest ping for this device
        $latestPing = LocationPing::where('device_id', $device->id)
            ->orderBy('ping_timestamp', 'desc')
            ->first();

        if (!$latestPing) {
            return response()->json([
                'error' => true,
                'message' => 'No location data found for this device',
                'code' => 'NO_LOCATION_DATA',
                'timestamp' => now()->timestamp * 1000,
            ], 404);
        }

        $twoMinutesAgo = now()->subMinutes(2);
        $isStale = $latestPing->received_at < $twoMinutesAgo;

        $location = [
            'deviceId' => $device->device_id,
            'name' => $latestPing->name,
            'latitude' => (float) $latestPing->latitude,
            'longitude' => (float) $latestPing->longitude,
            'accuracy' => $latestPing->accuracy,
            'batteryLevel' => $latestPing->battery_level,
            'signalStrength' => $latestPing->signal_strength,
            'microphoneStatus' => $latestPing->microphone_status,
            'cameraStatus' => $latestPing->camera_status,
            'recordingStatus' => $latestPing->recording_status,
            'lastUpdate' => $latestPing->ping_timestamp,
            'isStale' => $isStale,
            'avatar' => $device->avatar_data,
        ];

        return response()->json([
            'location' => $location,
        ], 200);
    }
}
