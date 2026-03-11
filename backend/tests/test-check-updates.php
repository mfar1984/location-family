<?php

/**
 * Test script for the check-updates endpoint
 * 
 * This script tests the polling endpoint that devices use to check for pending update requests
 */

$baseUrl = 'http://localhost:8000/api';

echo "=== Testing Check Updates Endpoint ===\n\n";

// Step 1: Register a test device
echo "1. Registering test device...\n";
$deviceId = 'test-device-' . time();
$registerData = [
    'name' => 'Test Device',
    'device_id' => $deviceId,
];

$ch = curl_init("$baseUrl/devices/register");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($registerData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Response ($httpCode): $response\n\n";

if ($httpCode !== 201) {
    echo "Failed to register device. Exiting.\n";
    exit(1);
}

// Step 2: Check for updates (should be none)
echo "2. Checking for updates (should be none)...\n";
$ch = curl_init("$baseUrl/devices/$deviceId/check-updates");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Response ($httpCode): $response\n";
$data = json_decode($response, true);
if ($data['updateRequested'] === false) {
    echo "✓ Correctly returned no pending updates\n\n";
} else {
    echo "✗ Expected no pending updates\n\n";
}

// Step 3: Trigger a manual update
echo "3. Triggering manual update...\n";
$ch = curl_init("$baseUrl/devices/$deviceId/update");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Response ($httpCode): $response\n\n";

// Step 4: Check for updates (should have one)
echo "4. Checking for updates (should have one)...\n";
$ch = curl_init("$baseUrl/devices/$deviceId/check-updates");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Response ($httpCode): $response\n";
$data = json_decode($response, true);
if ($data['updateRequested'] === true && isset($data['requestedAt'])) {
    echo "✓ Correctly returned pending update request\n\n";
} else {
    echo "✗ Expected pending update request\n\n";
}

// Step 5: Check for updates again (should be cleared)
echo "5. Checking for updates again (should be cleared after retrieval)...\n";
$ch = curl_init("$baseUrl/devices/$deviceId/check-updates");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Response ($httpCode): $response\n";
$data = json_decode($response, true);
if ($data['updateRequested'] === false) {
    echo "✓ Correctly cleared update request after retrieval\n\n";
} else {
    echo "✗ Expected update request to be cleared\n\n";
}

// Step 6: Test with non-existent device
echo "6. Testing with non-existent device...\n";
$ch = curl_init("$baseUrl/devices/non-existent-device/check-updates");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Response ($httpCode): $response\n";
if ($httpCode === 404) {
    echo "✓ Correctly returned 404 for non-existent device\n\n";
} else {
    echo "✗ Expected 404 for non-existent device\n\n";
}

echo "=== Test Complete ===\n";
