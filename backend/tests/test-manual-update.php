<?php
/**
 * Test manual update endpoint
 * Run with: php test-manual-update.php
 */

$baseUrl = 'http://localhost:8000/api';

echo "=== Manual Update Endpoint Test ===\n\n";

// Step 1: Register a test device
echo "Step 1: Register a test device\n";
$deviceId = 'test-device-' . time();
$registerData = [
    'name' => 'Test Device for Manual Update',
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
echo "✓ Device registered successfully\n\n";

// Step 2: Trigger manual update for the device
echo "Step 2: Trigger manual update\n";
$ch = curl_init($baseUrl . '/devices/' . $deviceId . '/update');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: $httpCode\n";
echo "Response: $response\n\n";

if ($httpCode !== 200) {
    echo "❌ Manual update trigger failed\n";
    exit(1);
}

$responseData = json_decode($response, true);
if (!isset($responseData['success']) || $responseData['success'] !== true) {
    echo "❌ Manual update response invalid\n";
    exit(1);
}

echo "✓ Manual update triggered successfully\n\n";

// Step 3: Test with non-existent device
echo "Step 3: Test with non-existent device\n";
$ch = curl_init($baseUrl . '/devices/non-existent-device/update');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: $httpCode\n";
echo "Response: $response\n\n";

if ($httpCode !== 404) {
    echo "❌ Should return 404 for non-existent device\n";
    exit(1);
}

echo "✓ Correctly returns 404 for non-existent device\n\n";

echo "=== All manual update tests passed! ✓ ===\n";
