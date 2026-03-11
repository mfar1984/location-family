<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Updating devices with avatars...\n\n";

$avatarMap = [
    'Mother' => 'woman',
    'Father' => 'man',
    'Sister' => 'girl',
    'Brother' => 'boy',
    'Grandmother' => 'old-woman',
    'Grandfather' => 'old-man',
    'Test Device' => 'person',
];

$devices = \App\Models\Device::all();

foreach ($devices as $device) {
    $avatarValue = $avatarMap[$device->name] ?? 'person';
    
    $device->update([
        'avatar_type' => 'icon',
        'avatar_value' => $avatarValue,
    ]);
    
    echo "✓ Updated {$device->name} with avatar: {$avatarValue}\n";
}

echo "\nDone! All devices now have avatars.\n";
