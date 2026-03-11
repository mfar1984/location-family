<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\LocationPing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that dashboard page loads successfully
     */
    public function test_dashboard_page_loads(): void
    {
        // Create a test user and authenticate
        $user = User::factory()->create([
            'username' => 'testuser',
            'email' => 'test@example.com',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
    }

    /**
     * Test that API returns locations in correct format for marker rendering
     * 
     * Requirements: 5.2 - Display Device_Markers for all active pilgrims
     */
    public function test_api_returns_locations_for_marker_rendering(): void
    {
        // Create test devices
        $device1 = Device::create([
            'device_id' => 'test-device-1',
            'name' => 'Test User 1',
            'is_active' => true,
        ]);

        $device2 = Device::create([
            'device_id' => 'test-device-2',
            'name' => 'Test User 2',
            'is_active' => true,
        ]);

        // Create recent location pings
        LocationPing::create([
            'device_id' => $device1->id,
            'name' => $device1->name,
            'latitude' => 21.4225,
            'longitude' => 39.8262,
            'accuracy' => 10.5,
            'battery_level' => 85,
            'signal_strength' => -70,
            'ping_timestamp' => now()->timestamp * 1000,
            'received_at' => now(),
        ]);

        LocationPing::create([
            'device_id' => $device2->id,
            'name' => $device2->name,
            'latitude' => 21.4230,
            'longitude' => 39.8270,
            'accuracy' => 12.0,
            'battery_level' => 90,
            'signal_strength' => -65,
            'ping_timestamp' => now()->timestamp * 1000,
            'received_at' => now(),
        ]);

        // Fetch locations
        $response = $this->getJson('/api/locations');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'locations' => [
                '*' => [
                    'deviceId',
                    'name',
                    'latitude',
                    'longitude',
                    'accuracy',
                    'batteryLevel',
                    'signalStrength',
                    'lastUpdate',
                    'isStale',
                ]
            ]
        ]);

        // Verify we have 2 locations
        $locations = $response->json('locations');
        $this->assertCount(2, $locations);

        // Verify each location has required fields for marker rendering
        foreach ($locations as $location) {
            $this->assertIsString($location['deviceId']);
            $this->assertIsString($location['name']);
            $this->assertIsFloat($location['latitude']);
            $this->assertIsFloat($location['longitude']);
            $this->assertIsInt($location['batteryLevel']);
            $this->assertIsInt($location['signalStrength']);
            $this->assertIsBool($location['isStale']);
        }
    }

    /**
     * Test that map bounds can be calculated from multiple device locations
     * 
     * Requirements: 5.6 - Center map view to show all active Device_Markers on initial load
     */
    public function test_multiple_devices_provide_bounds_for_map_fitting(): void
    {
        // Create multiple devices at different locations
        $devices = [
            ['lat' => 21.4200, 'lon' => 39.8200, 'name' => 'Device North'],
            ['lat' => 21.4250, 'lon' => 39.8250, 'name' => 'Device South'],
            ['lat' => 21.4225, 'lon' => 39.8180, 'name' => 'Device West'],
            ['lat' => 21.4225, 'lon' => 39.8340, 'name' => 'Device East'],
        ];

        foreach ($devices as $index => $deviceData) {
            $device = Device::create([
                'device_id' => "test-device-{$index}",
                'name' => $deviceData['name'],
                'is_active' => true,
            ]);

            LocationPing::create([
                'device_id' => $device->id,
                'name' => $device->name,
                'latitude' => $deviceData['lat'],
                'longitude' => $deviceData['lon'],
                'accuracy' => 10.0,
                'battery_level' => 80,
                'signal_strength' => -70,
                'ping_timestamp' => now()->timestamp * 1000,
                'received_at' => now(),
            ]);
        }

        // Fetch locations
        $response = $this->getJson('/api/locations');

        $response->assertStatus(200);
        $locations = $response->json('locations');
        
        // Verify we have all 4 devices
        $this->assertCount(4, $locations);

        // Calculate bounds from locations
        $latitudes = array_column($locations, 'latitude');
        $longitudes = array_column($locations, 'longitude');

        $minLat = min($latitudes);
        $maxLat = max($latitudes);
        $minLon = min($longitudes);
        $maxLon = max($longitudes);

        // Verify bounds encompass all devices
        $this->assertEquals(21.4200, $minLat);
        $this->assertEquals(21.4250, $maxLat);
        $this->assertEquals(39.8180, $minLon);
        $this->assertEquals(39.8340, $maxLon);

        // Verify each device location is within bounds
        foreach ($locations as $location) {
            $this->assertGreaterThanOrEqual($minLat, $location['latitude']);
            $this->assertLessThanOrEqual($maxLat, $location['latitude']);
            $this->assertGreaterThanOrEqual($minLon, $location['longitude']);
            $this->assertLessThanOrEqual($maxLon, $location['longitude']);
        }
    }

    /**
     * Test that different devices can be distinguished by their data
     * 
     * Requirements: 5.4 - Display Device_Markers with visual indicators distinguishing different pilgrims
     */
    public function test_devices_have_unique_identifiers_for_visual_distinction(): void
    {
        // Create devices with different names
        $deviceNames = ['Mother', 'Father', 'Child 1', 'Child 2'];
        
        foreach ($deviceNames as $index => $name) {
            $device = Device::create([
                'device_id' => "unique-device-{$index}",
                'name' => $name,
                'is_active' => true,
            ]);

            LocationPing::create([
                'device_id' => $device->id,
                'name' => $device->name,
                'latitude' => 21.4225 + ($index * 0.001),
                'longitude' => 39.8262 + ($index * 0.001),
                'accuracy' => 10.0,
                'battery_level' => 80,
                'signal_strength' => -70,
                'ping_timestamp' => now()->timestamp * 1000,
                'received_at' => now(),
            ]);
        }

        // Fetch locations
        $response = $this->getJson('/api/locations');

        $response->assertStatus(200);
        $locations = $response->json('locations');
        
        // Verify we have all devices
        $this->assertCount(4, $locations);

        // Verify each device has unique deviceId and name
        $deviceIds = array_column($locations, 'deviceId');
        $names = array_column($locations, 'name');

        $this->assertCount(4, array_unique($deviceIds), 'Device IDs should be unique');
        $this->assertCount(4, array_unique($names), 'Device names should be unique');

        // Verify names match expected values
        foreach ($deviceNames as $expectedName) {
            $this->assertContains($expectedName, $names);
        }
    }
}
