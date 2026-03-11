<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeviceRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test device registration with valid name and device_id
     */
    public function test_device_registration_with_valid_data(): void
    {
        $response = $this->postJson('/api/devices/register', [
            'name' => 'Test Device',
            'device_id' => 'test-device-123',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'deviceId' => 'test-device-123',
                'name' => 'Test Device',
                'registered' => true,
            ]);

        $this->assertDatabaseHas('devices', [
            'device_id' => 'test-device-123',
            'name' => 'Test Device',
            'is_active' => true,
        ]);
    }

    /**
     * Test device registration fails with empty name
     */
    public function test_device_registration_fails_with_empty_name(): void
    {
        $response = $this->postJson('/api/devices/register', [
            'name' => '',
            'device_id' => 'test-device-456',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => true,
                'message' => 'Validation failed',
            ]);
    }

    /**
     * Test device registration fails with missing name
     */
    public function test_device_registration_fails_with_missing_name(): void
    {
        $response = $this->postJson('/api/devices/register', [
            'device_id' => 'test-device-789',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => true,
                'message' => 'Validation failed',
            ]);
    }

    /**
     * Test device registration fails with duplicate device_id
     */
    public function test_device_registration_fails_with_duplicate_device_id(): void
    {
        // Create first device
        Device::create([
            'device_id' => 'duplicate-device',
            'name' => 'First Device',
            'registered_at' => now(),
            'is_active' => true,
        ]);

        // Try to register with same device_id
        $response = $this->postJson('/api/devices/register', [
            'name' => 'Second Device',
            'device_id' => 'duplicate-device',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => true,
                'message' => 'Validation failed',
            ]);
    }

    /**
     * Test listing all registered devices
     */
    public function test_list_all_registered_devices(): void
    {
        // Create test devices
        Device::create([
            'device_id' => 'device-1',
            'name' => 'Device One',
            'registered_at' => now(),
            'is_active' => true,
        ]);

        Device::create([
            'device_id' => 'device-2',
            'name' => 'Device Two',
            'registered_at' => now(),
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/devices');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'devices' => [
                    '*' => [
                        'id',
                        'device_id',
                        'name',
                        'registered_at',
                        'last_seen',
                        'is_active',
                    ],
                ],
            ])
            ->assertJsonCount(2, 'devices');
    }

    /**
     * Test listing devices only returns active devices
     */
    public function test_list_devices_only_returns_active_devices(): void
    {
        // Create active device
        Device::create([
            'device_id' => 'active-device',
            'name' => 'Active Device',
            'registered_at' => now(),
            'is_active' => true,
        ]);

        // Create inactive device
        Device::create([
            'device_id' => 'inactive-device',
            'name' => 'Inactive Device',
            'registered_at' => now(),
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/devices');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'devices')
            ->assertJsonFragment([
                'device_id' => 'active-device',
            ])
            ->assertJsonMissing([
                'device_id' => 'inactive-device',
            ]);
    }
}
