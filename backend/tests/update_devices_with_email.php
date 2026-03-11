<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Updating existing devices with email...\n\n";

$devices = \App\Models\Device::all();

if ($devices->count() === 0) {
    echo "No devices found.\n";
    exit;
}

// Update all existing devices with a default email
$defaultEmail = 'family@example.com';

foreach ($devices as $device) {
    $device->update(['email' => $defaultEmail]);
    echo "✓ Updated {$device->name} with email: {$defaultEmail}\n";
}

echo "\nDone! All devices now have email: {$defaultEmail}\n";
echo "You can login to dashboard using this email.\n";
