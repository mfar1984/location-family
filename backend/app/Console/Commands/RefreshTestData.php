<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;
use App\Models\LocationPing;

class RefreshTestData extends Command
{
    protected $signature = 'test:refresh-data';
    protected $description = 'Refresh test location data for demo purposes';

    public function handle()
    {
        $this->info('Refreshing test location data...');
        
        $devices = Device::where('is_active', true)->get();
        
        if ($devices->count() === 0) {
            $this->error('No active devices found');
            return 1;
        }
        
        foreach ($devices as $device) {
            // Generate random location around Mecca
            $baseLat = 21.4225;
            $baseLon = 39.8262;
            
            $lat = $baseLat + (rand(-100, 100) / 10000); // ±0.01 degrees
            $lon = $baseLon + (rand(-100, 100) / 10000);
            
            LocationPing::create([
                'device_id' => $device->id,
                'name' => $device->name,
                'latitude' => $lat,
                'longitude' => $lon,
                'accuracy' => rand(5, 20),
                'battery_level' => rand(20, 100),
                'signal_strength' => rand(-90, -50),
                'ping_timestamp' => now()->timestamp * 1000,
                'received_at' => now(),
            ]);
            
            $this->info("✓ Refreshed data for {$device->name}");
        }
        
        $this->info('Test data refreshed successfully!');
        return 0;
    }
}