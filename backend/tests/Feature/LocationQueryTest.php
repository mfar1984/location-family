<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Device;
use App\Models\LocationPing;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LocationQueryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test getting all active device locations
     */
    public function test_get_all_active_device_locations(): void
    {
        // Create test devices
        $device1 = Device::create([
            'device_id' => 'device-1',
            'name' => 'Device One',
            'registered_at' => now(),
            'is_active' => true,
        ]);

        $device2 = Device::create([
            'device_id' => 'device-2',
            'name' => 'Device Two',
            'registered_at' => now(),
            'is_active' => true,
        ]);

        // Create recent pings (within 5 minutes)
        LocationPing::create([
            'device_id' => $device1->id,
            'name' => 'Device One',
            'latitude' => 21.4225,
            'longitude' => 39.8262,
            'accuracy' => 15.5,
            'battery_level' => 85,
            'signal_strength' => -70,
            'ping_timestamp' => now()->timestamp * 1000,
            'received_at' => now(),
        ]);

        LocationPing::create([
            'device_id' => $device2->id,
            'name' => 'Device Two',
            'latitude' => 21.4230,
            'longitude' => 39.8270,
            'accuracy' => 20.0,
            'battery_level' => 70,
            'signal_strength' => -75,
            'ping_timestamp' => now()->timestamp * 1000,
            'received_at' => now(),
        ]);

        $response = $this->getJson('/api/locations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'locations' => [
                    '*' => [
                        'deviceId',
                        'name',
                        'latitude',
                        'longitude',
                        'accuracy',
                        'batteryLevel',
                        'signalStrength',
                        'microphoneStatus',
                        'cameraStatus',
                        'recordingStatus',
                        'lastUpdate',
                        'isStale',
                    ],
                ],
            ])
            ->assertJsonCount(2, 'locations');
    }

    /**
     * Test getting all locations only returns pings from last 5 minutes
     */
    public function test_get_locations_only_returns_recent_pings(): void
    {
        $device = Device::create([
            'device_id' => 'device-1',
            'name' => 'Device One',
            'registered_at' => now(),
            'is_active' => true,
        ]);

        // Create recent ping (within 5 minutes)
        LocationPing::create([
            'device_id' => $device->id,
            'name' => 'Device One',
            'latitude' => 21.4225,
            'longitude' => 39.8262,
            'accuracy' => 15.5,
            'battery_level' => 85,
            'signal_strength' => -70,
            'ping_timestamp' => now()->timestamp * 1000,
            'received_at' => now(),
        ]);

        // Create old ping (more than 5 minutes ago)
        LocationPing::create([
            'device_id' => $device->id,
            'name' => 'Device One',
            'latitude' => 21.4200,
            'longitude' => 39.8250,
            'accuracy' => 15.5,
            'battery_level' => 90,
            'signal_strength' => -65,
            'ping_timestamp' => now()->subMinutes(10)->timestamp * 1000,
            'received_at' => now()->subMinutes(10),
        ]);

        $response = $this->getJson('/api/locations');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'locations')
            ->assertJsonFragment([
                'latitude' => 21.4225,
            ]);
    }

    /**
     * Test stale marker indication for devices not updated in 2+ minutes
     */
    public function test_stale_marker_indication(): void
    {
        $device = Device::create([
            'device_id' => 'device-1',
            'name' => 'Device One',
            'registered_at' => now(),
            'is_active' => true,
        ]);

        // Create ping that's 3 minutes old (stale but within 5 minute window)
        LocationPing::create([
            'device_id' => $device->id,
            'name' => 'Device One',
            'latitude' => 21.4225,
            'longitude' => 39.8262,
            'accuracy' => 15.5,
            'battery_level' => 85,
            'signal_strength' => -70,
            'ping_timestamp' => now()->subMinutes(3)->timestamp * 1000,
            'received_at' => now()->subMinutes(3),
        ]);

        $response = $this->getJson('/api/locations');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'isStale' => true,
            ]);
    }

    /**
     * Test getting specific device location
     */
    public function test_get_specific_device_location(): void
    {
        $device = Device::create([
            'device_id' => 'device-1',
            'name' => 'Device One',
            'registered_at' => now(),
            'is_active' => true,
        ]);

        LocationPing::create([
            'device_id' => $device->id,
            'name' => 'Device One',
            'latitude' => 21.4225,
            'longitude' => 39.8262,
            'accuracy' => 15.5,
            'battery_level' => 85,
            'signal_strength' => -70,
            'microphone_status' => false,
            'camera_status' => false,
            'recording_status' => false,
            'ping_timestamp' => now()->timestamp * 1000,
            'received_at' => now(),
        ]);

        $response = $this->getJson('/api/locations/device-1');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'location' => [
                    'deviceId',
                    'name',
                    'latitude',
                    'longitude',
                    'accuracy',
                    'batteryLevel',
                    'signalStrength',
                    'microphoneStatus',
                    'cameraStatus',
                    'recordingStatus',
                    'lastUpdate',
                    'isStale',
                ],
            ])
            ->assertJsonFragment([
                'deviceId' => 'device-1',
                'name' => 'Device One',
                'latitude' => 21.4225,
                'longitude' => 39.8262,
            ]);
    }

    /**
     * Test getting specific device location returns 404 for non-existent device
     */
    public function test_get_device_location_returns_404_for_non_existent_device(): void
    {
        $response = $this->getJson('/api/locations/non-existent-device');

        $response->assertStatus(404)
            ->assertJson([
                'error' => true,
                'message' => 'Device not found',
                'code' => 'DEVICE_NOT_FOUND',
            ]);
    }

    /**
     * Test getting specific device location returns 404 when no location data exists
     */
    public function test_get_device_location_returns_404_when_no_location_data(): void
    {
        Device::create([
            'device_id' => 'device-1',
            'name' => 'Device One',
            'registered_at' => now(),
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/locations/device-1');

        $response->assertStatus(404)
            ->assertJson([
                'error' => true,
                'message' => 'No location data found for this device',
                'code' => 'NO_LOCATION_DATA',
            ]);
    }

    /**
     * Test getting latest ping when multiple pings exist
     */
    public function test_get_latest_ping_when_multiple_exist(): void
    {
        $device = Device::create([
            'device_id' => 'device-1',
            'name' => 'Device One',
            'registered_at' => now(),
            'is_active' => true,
        ]);

        // Create older ping
        LocationPing::create([
            'device_id' => $device->id,
            'name' => 'Device One',
            'latitude' => 21.4200,
            'longitude' => 39.8250,
            'accuracy' => 15.5,
            'battery_level' => 90,
            'signal_strength' => -65,
            'ping_timestamp' => now()->subMinutes(2)->timestamp * 1000,
            'received_at' => now()->subMinutes(2),
        ]);

        // Create newer ping
        LocationPing::create([
            'device_id' => $device->id,
            'name' => 'Device One',
            'latitude' => 21.4225,
            'longitude' => 39.8262,
            'accuracy' => 15.5,
            'battery_level' => 85,
            'signal_strength' => -70,
            'ping_timestamp' => now()->timestamp * 1000,
            'received_at' => now(),
        ]);

        $response = $this->getJson('/api/locations/device-1');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'latitude' => 21.4225,
                'batteryLevel' => 85,
            ]);
    }
}
