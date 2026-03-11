<?php

// Register multiple test devices

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Device;

$deviceNames = [
    'Mother',
    'Father',
    'Sister',
    'Brother',
    'Grandmother'
];

echo "Registering multiple test devices...\n\n";

foreach ($deviceNames as $name) {
    $deviceId = 'device-' . strtolower($name) . '-' . time() . rand(100, 999);
    
    $device = Device::create([
        'device_id' => $deviceId,
        'name' => $name,
        'registered_at' => now(),
        'is_active' => true,
    ]);
    
    echo "✓ Registered device: {$name} (ID: {$deviceId})\n";
}

echo "\nDone! Registered " . count($deviceNames) . " devices.\n";
