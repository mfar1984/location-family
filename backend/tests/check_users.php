<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->boot();

echo "Users in database:\n";
echo "ID | Username | Email\n";
echo "-------------------\n";

$users = App\Models\User::all();
foreach ($users as $user) {
    echo $user->id . " | " . $user->username . " | " . $user->email . "\n";
}

echo "\nTotal users: " . $users->count() . "\n";