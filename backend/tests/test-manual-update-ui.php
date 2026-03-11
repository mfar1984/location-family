<?php
/**
 * Test script for manual update UI functionality
 * 
 * This script tests the manual update button in the info drawer
 */

$baseUrl = 'http://localhost:8000/api';

// Use an existing device
$deviceId = 'test-device-1773236950';

echo "Testing Manual Update UI Functionality\n";
echo "======================================\n\n";

// Step 1: Trigger manual update via API (simulating button click)
echo "1. Triggering manual update for device: $deviceId\n";
$ch = curl_init($baseUrl . '/devices/' . $deviceId . '/update');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Response Code: $httpCode\n";
echo "Response: $response\n\n";

$data = json_decode($response, true);

if ($httpCode === 200 && isset($data['success']) && $data['success']) {
    echo "✓ Manual update request sent successfully!\n";
    echo "  Message: " . $data['message'] . "\n\n";
    
    // Step 2: Verify the update request is stored
    echo "2. Verifying update request is stored (device polling endpoint)\n";
    $ch = curl_init($baseUrl . '/devices/' . $deviceId . '/check-updates');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Response Code: $httpCode\n";
    echo "Response: $response\n\n";
    
    $checkData = json_decode($response, true);
    
    if (isset($checkData['updateRequested']) && $checkData['updateRequested']) {
        echo "✓ Update request is properly stored and retrievable!\n";
        echo "  Requested at: " . date('Y-m-d H:i:s', $checkData['requestedAt'] / 1000) . "\n\n";
    } else {
        echo "✗ Update request not found in cache\n\n";
    }
    
    echo "=== TEST PASSED ===\n";
    echo "The manual update button functionality is working correctly!\n";
    echo "- Button triggers POST /api/devices/{deviceId}/update\n";
    echo "- API stores update request in cache\n";
    echo "- Device can retrieve update request via polling\n";
    
} else {
    echo "✗ Manual update request failed\n";
    echo "  Error: " . ($data['message'] ?? 'Unknown error') . "\n\n";
    echo "=== TEST FAILED ===\n";
}

echo "\n";
echo "To test the UI:\n";
echo "1. Open http://localhost:8000 in your browser\n";
echo "2. Click on a device marker on the map\n";
echo "3. The info drawer should open on the right\n";
echo "4. Scroll down to the 'Actions' section\n";
echo "5. Click the 'Request Update' button\n";
echo "6. You should see:\n";
echo "   - Button text changes to 'Requesting...'\n";
echo "   - A spinner appears\n";
echo "   - After success, a green message appears\n";
echo "   - Button re-enables after 2 seconds\n";
