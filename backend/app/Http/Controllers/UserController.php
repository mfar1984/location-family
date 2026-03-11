<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Device;

class UserController extends Controller
{
    /**
     * Get user's devices
     */
    public function getDevices()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        $devices = Device::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'device_id', 'name', 'avatar_type', 'avatar_value', 'is_active', 'registered_at']);
        
        return response()->json([
            'devices' => $devices
        ]);
    }
    
    /**
     * Delete a device
     */
    public function deleteDevice($deviceId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        $device = Device::where('device_id', $deviceId)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$device) {
            return response()->json([
                'error' => true,
                'message' => 'Device not found'
            ], 404);
        }
        
        // Delete associated location pings
        $device->locationPings()->delete();
        
        // Delete device
        $device->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Device deleted successfully'
        ]);
    }
    
    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        $request->validate([
            'username' => 'required|string|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);
        
        $user->update([
            'username' => $request->username,
            'email' => $request->email,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully'
        ]);
    }
    
    /**
     * Update device name
     */
    public function updateDeviceName($deviceId, Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        $request->validate([
            'name' => 'required|string|min:1|max:255',
        ]);
        
        $device = Device::where('device_id', $deviceId)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$device) {
            return response()->json([
                'error' => true,
                'message' => 'Device not found'
            ], 404);
        }
        
        $device->update([
            'name' => $request->name
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Device name updated successfully',
            'device' => [
                'device_id' => $device->device_id,
                'name' => $device->name
            ]
        ]);
    }
    /**
     * Get user profile data
     */
    public function getProfileData()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        return response()->json([
            'username' => $user->username,
            'email' => $user->email,
            'avatar_type' => $user->avatar_type,
            'avatar_value' => $user->avatar_value,
        ]);
    }
    
    /**
     * Update user avatar
     */
    public function updateAvatar(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        $request->validate([
            'avatar_type' => 'required|in:icon,upload',
            'avatar_value' => 'required|string',
        ]);
        
        $user->update([
            'avatar_type' => $request->avatar_type,
            'avatar_value' => $request->avatar_value,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Avatar updated successfully'
        ]);
    }
    
    /**
     * Generate verification code for device deletion
     */
    public function generateVerificationCode($deviceId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        $device = Device::where('device_id', $deviceId)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$device) {
            return response()->json([
                'error' => true,
                'message' => 'Device not found'
            ], 404);
        }
        
        $code = $device->generateVerificationCode();
        
        return response()->json([
            'success' => true,
            'verification_code' => $code,
            'expires_at' => $device->verification_code_expires_at->toISOString(),
            'message' => 'Verification code generated successfully'
        ]);
    }
    
    /**
     * Delete a device with verification code
     */
    public function deleteDeviceWithCode($deviceId, Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        $request->validate([
            'verification_code' => 'required|string|size:8',
        ]);
        
        $device = Device::where('device_id', $deviceId)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$device) {
            return response()->json([
                'error' => true,
                'message' => 'Device not found'
            ], 404);
        }
        
        if (!$device->isVerificationCodeValid($request->verification_code)) {
            return response()->json([
                'error' => true,
                'message' => 'Invalid or expired verification code'
            ], 400);
        }
        
        // Delete associated location pings
        $device->locationPings()->delete();
        
        // Delete device
        $device->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Device deleted successfully'
        ]);
    }
    
    public function changePassword(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);
        
        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'error' => true,
                'message' => 'Current password is incorrect'
            ], 400);
        }
        
        // Update password
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }
}