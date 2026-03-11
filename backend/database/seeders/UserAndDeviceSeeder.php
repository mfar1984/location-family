<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Device;

class UserAndDeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test user
        $user = User::firstOrCreate(
            ['email' => 'family@example.com'],
            [
                'username' => 'family',
                'name' => 'Family User',
                'password' => Hash::make('password123'),
            ]
        );

        echo "Created/Found user: {$user->username} (ID: {$user->id})\n";

        // Update all existing devices to link to this user
        $devicesUpdated = Device::whereNull('user_id')
            ->orWhere('user_id', 0)
            ->update(['user_id' => $user->id]);

        echo "Updated {$devicesUpdated} devices to link to user ID {$user->id}\n";

        // Show all devices
        $devices = Device::where('user_id', $user->id)->get();
        echo "\nDevices linked to {$user->username}:\n";
        foreach ($devices as $device) {
            echo "  - {$device->name} ({$device->device_id})\n";
        }
    }
}
