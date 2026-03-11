<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Adding fresh location pings...\n\n";

$devices = \App\Models\Device::all();

if ($devices->count() === 0) {
    echo "No devices found. Please register devices first.\n";
    exit;
}

echo "Found {$devices->count()} devices. Adding fresh pings...\n\n";

foreach ($devices as $device) {
    // Add a fresh ping (within last 5 minutes)
    $ping = \App\Models\LocationPing::create([
        'device_id' => $device->id,
        'name' => $device->name,
        'latitude' => 21.4225 + (rand(-100, 100) / 10000), // Random around Mecca
        'longitude' => 39.8262 + (rand(-100, 100) / 10000),
        'accuracy' => rand(10, 30),
        'battery_level' => rand(50, 100),
        'signal_strength' => rand(-90, -50),
        'microphone_status' => false,
        'camera_status' => false,
        'recording_status' => false,
        'ping_timestamp' => now()->timestamp * 1000,
        'received_at' => now(),
    ]);
    
    // Update device last_seen
    $device->update(['last_seen' => now()]);
    
    echo "✓ Added ping for {$device->name} at ({$ping->latitude}, {$ping->longitude})\n";
}

echo "\nDone! All devices now have fresh pings.\n";
