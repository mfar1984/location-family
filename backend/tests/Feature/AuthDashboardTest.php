<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\LocationPing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthDashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that dashboard page loads successfully with authentication
     */
    public function test_dashboard_page_loads_with_auth(): void
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
     * Test that unauthenticated users are redirected to login
     */
    public function test_dashboard_redirects_unauthenticated_users(): void
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Test that API returns locations for authenticated user only
     */
    public function test_api_returns_user_locations_only(): void
    {
        // Create two users
        $user1 = User::factory()->create(['username' => 'user1', 'email' => 'user1@example.com']);
        $user2 = User::factory()->create(['username' => 'user2', 'email' => 'user2@example.com']);

        // Create devices for each user
        $device1 = Device::create([
            'device_id' => 'device-user1',
            'name' => 'User 1 Device',
            'user_id' => $user1->id,
            'is_active' => true,
        ]);

        $device2 = Device::create([
            'device_id' => 'device-user2',
            'name' => 'User 2 Device',
            'user_id' => $user2->id,
            'is_active' => true,
        ]);

        // Create location pings for both devices
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

        // Test as user1 - should only see user1's devices
        $response = $this->actingAs($user1)->getJson('/api/locations');

        $response->assertStatus(200);
        $locations = $response->json('locations');
        
        $this->assertCount(1, $locations);
        $this->assertEquals('device-user1', $locations[0]['deviceId']);
        $this->assertEquals('User 1 Device', $locations[0]['name']);

        // Test as user2 - should only see user2's devices
        $response = $this->actingAs($user2)->getJson('/api/locations');

        $response->assertStatus(200);
        $locations = $response->json('locations');
        
        $this->assertCount(1, $locations);
        $this->assertEquals('device-user2', $locations[0]['deviceId']);
        $this->assertEquals('User 2 Device', $locations[0]['name']);
    }

    /**
     * Test that unauthenticated API calls are rejected
     */
    public function test_api_rejects_unauthenticated_requests(): void
    {
        $response = $this->getJson('/api/locations');

        $response->assertStatus(401);
        $response->assertJson([
            'error' => true,
            'message' => 'Unauthorized',
            'code' => 'UNAUTHORIZED',
        ]);
    }

    /**
     * Test login functionality
     */
    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test login with wrong credentials
     */
    public function test_login_fails_with_wrong_credentials(): void
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $this->assertGuest();
    }
}