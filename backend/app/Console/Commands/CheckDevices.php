<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Device;
use App\Models\LocationPing;

class CheckDevices extends Command
{
    protected $signature = 'check:devices';
    protected $description = 'Check devices and their relationships';

    public function handle()
    {
        $user = User::where('username', 'family')->first();
        
        if (!$user) {
            $this->error('User "family" not found');
            return 1;
        }
        
        $this->info("User: {$user->username} (ID: {$user->id}, Email: {$user->email})");
        $this->info('');
        
        $this->info('All devices in database:');
        $this->info('ID | Device ID | Name | User ID | Active');
        $this->info('----------------------------------------');
        
        $allDevices = Device::all();
        foreach ($allDevices as $device) {
            $this->info($device->id . ' | ' . $device->device_id . ' | ' . $device->name . ' | ' . ($device->user_id ?? 'NULL') . ' | ' . ($device->is_active ? 'Yes' : 'No'));
        }
        
        $this->info('');
        $this->info("Devices linked to user {$user->username}:");
        
        $userDevices = Device::where('user_id', $user->id)->get();
        if ($userDevices->count() > 0) {
            foreach ($userDevices as $device) {
                $this->info("- {$device->name} ({$device->device_id})");
                
                // Check for recent location pings
                $recentPings = LocationPing::where('device_id', $device->id)
                    ->where('received_at', '>=', now()->subMinutes(5))
                    ->count();
                    
                $this->info("  Recent pings (last 5 min): {$recentPings}");
            }
        } else {
            $this->error("No devices found for user {$user->username}");
        }
        
        return 0;
    }
}