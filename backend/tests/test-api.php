<?php
/**
 * Simple API endpoint tester
 * Run with: php test-api.php
 */

$baseUrl = 'http://localhost:8000/api';

echo "=== Umrah Family Tracker API Test ===\n\n";

// Test 1: Register a device
echo "Test 1: Register a device\n";
$deviceId = 'test-device-' . time();
$registerData = [
    'name' => 'Test Device',
    'device_id' => $deviceId
];

$ch = curl_init($baseUrl . '/devices/register');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($registerData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: $httpCode\n";
echo "Response: $response\n\n";

if ($httpCode !== 201) {
    echo "❌ Device registration failed\n";
    exit(1);
}
echo "✓ Device registration successful\n\n";

// Test 2: List all devices
echo "Test 2: List all devices\n";
$ch = curl_init($baseUrl . '/devices');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: $httpCode\n";
echo "Response: $response\n\n";

if ($httpCode !== 200) {
    echo "❌ List devices failed\n";
    exit(1);
}
echo "✓ List devices successful\n\n";

// Test 3: Send a location ping
echo "Test 3: Send a location ping\n";
$pingData = [
    'deviceId' => $deviceId,
    'name' => 'Test Device',
    'latitude' => 21.4225,
    'longitude' => 39.8262,
    'accuracy' => 15.5,
    'batteryLevel' => 85,
    'signalStrength' => -70,
    'microphoneStatus' => false,
    'cameraStatus' => false,
    'recordingStatus' => false,
    'timestamp' => time() * 1000
];

$ch = curl_init($baseUrl . '/pings');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($pingData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: $httpCode\n";
echo "Response: $response\n\n";

if ($httpCode !== 201) {
    echo "❌ Send ping failed\n";
    exit(1);
}
echo "✓ Send ping successful\n\n";

// Test 4: Get all locations
echo "Test 4: Get all locations\n";
$ch = curl_init($baseUrl . '/locations');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: $httpCode\n";
echo "Response: $response\n\n";

if ($httpCode !== 200) {
    echo "❌ Get locations failed\n";
    exit(1);
}
echo "✓ Get locations successful\n\n";

// Test 5: Get specific device location
echo "Test 5: Get specific device location\n";
$ch = curl_init($baseUrl . '/locations/' . $deviceId);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: $httpCode\n";
echo "Response: $response\n\n";

if ($httpCode !== 200) {
    echo "❌ Get device location failed\n";
    exit(1);
}
echo "✓ Get device location successful\n\n";

echo "=== All API tests passed! ✓ ===\n";
