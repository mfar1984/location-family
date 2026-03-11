<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Device;
use App\Models\LocationPing;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LocationPingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful ping storage with valid data
     */
    public function test_store_ping_with_valid_data(): void
    {
        // Create a device first
        $device = Device::create([
            'device_id' => 'test-device-123',
            'name' => 'Test Device',
            'registered_at' => now(),
            'is_active' => true,
        ]);

        $pingData = [
            'deviceId' => 'test-device-123',
            'name' => 'Test Device',
            'latitude' => 21.4225,
            'longitude' => 39.8262,
            'accuracy' => 15.5,
            'batteryLevel' => 85,
            'signalStrength' => -70,
            'microphoneStatus' => false,
            'cameraStatus' => false,
            'recordingStatus' => false,
            'timestamp' => 1704067200000,
        ];

        $response = $this->postJson('/api/pings', $pingData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'ping_id',
            ]);

        // Verify ping was stored in database
        $this->assertDatabaseHas('location_pings', [
            'device_id' => $device->id,
            'name' => 'Test Device',
            'latitude' => 21.4225,
            'longitude' => 39.8262,
            'battery_level' => 85,
        ]);

        // Verify device last_seen was updated
        $device->refresh();
        $this->assertNotNull($device->last_seen);
    }

    /**
     * Test ping storage fails with invalid latitude
     */
    public function test_store_ping_fails_with_invalid_latitude(): void
    {
        $device = Device::create([
            'device_id' => 'test-device-123',
            'name' => 'Test Device',
            'registered_at' => now(),
            'is_active' => true,
        ]);

        // Test latitude > 90
        $pingData = [
            'deviceId' => 'test-device-123',
            'name' => 'Test Device',
            'latitude' => 95.0,
            'longitude' => 39.8262,
            'batteryLevel' => 85,
            'timestamp' => 1704067200000,
        ];

        $response = $this->postJson('/api/pings', $pingData);
        $response->assertStatus(422)
            ->assertJson([
                'error' => true,
                'message' => 'Validation failed',
            ]);

        // Test latitude < -90
        $pingData['latitude'] = -95.0;
        $response = $this->postJson('/api/pings', $pingData);
        $response->assertStatus(422);
    }

    /**
     * Test ping storage fails with invalid longitude
     */
    public function test_store_ping_fails_with_invalid_longitude(): void
    {
        $device = Device::create([
            'device_id' => 'test-device-123',
            'name' => 'Test Device',
            'registered_at' => now(),
            'is_active' => true,
        ]);

        // Test longitude > 180
        $pingData = [
            'deviceId' => 'test-device-123',
            'name' => 'Test Device',
            'latitude' => 21.4225,
            'longitude' => 185.0,
            'batteryLevel' => 85,
            'timestamp' => 1704067200000,
        ];

        $response = $this->postJson('/api/pings', $pingData);
        $response->assertStatus(422)
            ->assertJson([
                'error' => true,
                'message' => 'Validation failed',
            ]);

        // Test longitude < -180
        $pingData['longitude'] = -185.0;
        $response = $this->postJson('/api/pings', $pingData);
        $response->assertStatus(422);
    }

    /**
     * Test ping storage fails with invalid battery level
     */
    public function test_store_ping_fails_with_invalid_battery_level(): void
    {
        $device = Device::create([
            'device_id' => 'test-device-123',
            'name' => 'Test Device',
            'registered_at' => now(),
            'is_active' => true,
        ]);

        // Test battery > 100
        $pingData = [
            'deviceId' => 'test-device-123',
            'name' => 'Test Device',
            'latitude' => 21.4225,
            'longitude' => 39.8262,
            'batteryLevel' => 105,
            'timestamp' => 1704067200000,
        ];

        $response = $this->postJson('/api/pings', $pingData);
        $response->assertStatus(422);

        // Test battery < 0
        $pingData['batteryLevel'] = -5;
        $response = $this->postJson('/api/pings', $pingData);
        $response->assertStatus(422);
    }

    /**
     * Test ping storage fails with empty name
     */
    public function test_store_ping_fails_with_empty_name(): void
    {
        $device = Device::create([
            'device_id' => 'test-device-123',
            'name' => 'Test Device',
            'registered_at' => now(),
            'is_active' => true,
        ]);

        $pingData = [
            'deviceId' => 'test-device-123',
            'name' => '',
            'latitude' => 21.4225,
            'longitude' => 39.8262,
            'batteryLevel' => 85,
            'timestamp' => 1704067200000,
        ];

        $response = $this->postJson('/api/pings', $pingData);
        $response->assertStatus(422)
            ->assertJson([
                'error' => true,
                'message' => 'Validation failed',
            ]);
    }

    /**
     * Test ping storage fails for non-existent device
     */
    public function test_store_ping_fails_for_nonexistent_device(): void
    {
        $pingData = [
            'deviceId' => 'nonexistent-device',
            'name' => 'Test Device',
            'latitude' => 21.4225,
            'longitude' => 39.8262,
            'batteryLevel' => 85,
            'timestamp' => 1704067200000,
        ];

        $response = $this->postJson('/api/pings', $pingData);
        $response->assertStatus(404)
            ->assertJson([
                'error' => true,
                'message' => 'Device not found',
                'code' => 'DEVICE_NOT_FOUND',
            ]);
    }

    /**
     * Test ping storage with boundary coordinate values
     */
    public function test_store_ping_with_boundary_coordinates(): void
    {
        $device = Device::create([
            'device_id' => 'test-device-123',
            'name' => 'Test Device',
            'registered_at' => now(),
            'is_active' => true,
        ]);

        // Test North Pole
        $pingData = [
            'deviceId' => 'test-device-123',
            'name' => 'Test Device',
            'latitude' => 90.0,
            'longitude' => 0.0,
            'batteryLevel' => 85,
            'timestamp' => 1704067200000,
        ];

        $response = $this->postJson('/api/pings', $pingData);
        $response->assertStatus(201);

        // Test South Pole
        $pingData['latitude'] = -90.0;
        $response = $this->postJson('/api/pings', $pingData);
        $response->assertStatus(201);

        // Test International Date Line
        $pingData['latitude'] = 0.0;
        $pingData['longitude'] = 180.0;
        $response = $this->postJson('/api/pings', $pingData);
        $response->assertStatus(201);

        $pingData['longitude'] = -180.0;
        $response = $this->postJson('/api/pings', $pingData);
        $response->assertStatus(201);
    }

    /**
     * Test ping storage with optional fields
     */
    public function test_store_ping_with_optional_fields(): void
    {
        $device = Device::create([
            'device_id' => 'test-device-123',
            'name' => 'Test Device',
            'registered_at' => now(),
            'is_active' => true,
        ]);

        // Test with all optional fields
        $pingData = [
            'deviceId' => 'test-device-123',
            'name' => 'Test Device',
            'latitude' => 21.4225,
            'longitude' => 39.8262,
            'accuracy' => 15.5,
            'batteryLevel' => 85,
            'signalStrength' => -70,
            'microphoneStatus' => true,
            'cameraStatus' => true,
            'recordingStatus' => true,
            'timestamp' => 1704067200000,
        ];

        $response = $this->postJson('/api/pings', $pingData);
        $response->assertStatus(201);

        $this->assertDatabaseHas('location_pings', [
            'device_id' => $device->id,
            'microphone_status' => true,
            'camera_status' => true,
            'recording_status' => true,
        ]);

        // Test without optional fields
        $pingData = [
            'deviceId' => 'test-device-123',
            'name' => 'Test Device',
            'latitude' => 21.4225,
            'longitude' => 39.8262,
            'batteryLevel' => 85,
            'timestamp' => 1704067200000,
        ];

        $response = $this->postJson('/api/pings', $pingData);
        $response->assertStatus(201);
    }

    /**
     * Test ping storage updates device last_seen timestamp
     */
    public function test_store_ping_updates_device_last_seen(): void
    {
        $device = Device::create([
            'device_id' => 'test-device-123',
            'name' => 'Test Device',
            'registered_at' => now(),
            'is_active' => true,
            'last_seen' => null,
        ]);

        $this->assertNull($device->last_seen);

        $pingData = [
            'deviceId' => 'test-device-123',
            'name' => 'Test Device',
            'latitude' => 21.4225,
            'longitude' => 39.8262,
            'batteryLevel' => 85,
            'timestamp' => 1704067200000,
        ];

        $response = $this->postJson('/api/pings', $pingData);
        $response->assertStatus(201);

        $device->refresh();
        $this->assertNotNull($device->last_seen);
        $this->assertTrue($device->last_seen->isToday());
    }
}
