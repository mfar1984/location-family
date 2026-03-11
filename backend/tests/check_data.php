<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking database data...\n\n";

$devices = \App\Models\Device::all();
echo "Total Devices: " . $devices->count() . "\n\n";

if ($devices->count() > 0) {
    echo "Devices:\n";
    foreach ($devices as $device) {
        echo "- {$device->device_id} ({$device->name})\n";
    }
} else {
    echo "No devices found in database.\n";
}

echo "\n";

$pings = \App\Models\LocationPing::all();
echo "Total Location Pings: " . $pings->count() . "\n";

if ($pings->count() > 0) {
    echo "\nRecent pings:\n";
    $recentPings = \App\Models\LocationPing::orderBy('received_at', 'desc')->limit(5)->get();
    foreach ($recentPings as $ping) {
        echo "- Device: {$ping->name}, Lat: {$ping->latitude}, Lng: {$ping->longitude}, Time: {$ping->received_at}\n";
    }
}
