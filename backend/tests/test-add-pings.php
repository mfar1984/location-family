<?php

// Add test location pings for testing the dashboard

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Device;
use App\Models\LocationPing;

// Get all devices
$devices = Device::all();

if ($devices->isEmpty()) {
    echo "No devices found. Please register devices first.\n";
    exit(1);
}

// Mecca coordinates with slight variations
$baseLatitude = 21.4225;
$baseLongitude = 39.8262;

echo "Adding fresh location pings for " . $devices->count() . " devices...\n\n";

foreach ($devices as $index => $device) {
    // Create slight variations in location
    $latOffset = (rand(-100, 100) / 10000); // ±0.01 degrees
    $lonOffset = (rand(-100, 100) / 10000);
    
    $ping = LocationPing::create([
        'device_id' => $device->id,
        'name' => $device->name,
        'latitude' => $baseLatitude + $latOffset,
        'longitude' => $baseLongitude + $lonOffset,
        'accuracy' => rand(5, 20),
        'battery_level' => rand(50, 100),
        'signal_strength' => rand(-90, -50),
        'microphone_status' => (bool)rand(0, 1),
        'camera_status' => (bool)rand(0, 1),
        'recording_status' => false,
        'ping_timestamp' => now()->timestamp * 1000,
        'received_at' => now(),
    ]);
    
    echo "✓ Added ping for device: {$device->name} (ID: {$device->device_id})\n";
    echo "  Location: {$ping->latitude}, {$ping->longitude}\n";
    echo "  Battery: {$ping->battery_level}%, Signal: {$ping->signal_strength} dBm\n\n";
}

echo "Done! All devices now have fresh location data.\n";
