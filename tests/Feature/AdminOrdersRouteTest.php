<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Laravel\Sanctum\Sanctum;

class AdminOrdersRouteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin role
        $adminRole = Role::create([
            'name' => 'Admin',
            'description' => 'Administrator role'
        ]);
        
        // Create admin user
        $adminUser = User::factory()->create([
            'email' => 'admin@koumbaya.com',
            'first_name' => 'Admin',
            'last_name' => 'User',
        ]);
        
        $adminUser->roles()->attach($adminRole);
        
        Sanctum::actingAs($adminUser);
    }

    public function test_admin_orders_route_exists()
    {
        $response = $this->getJson('/api/admin/orders');
        
        // Route should exist (not return 404)
        $this->assertNotEquals(404, $response->status());
        
        // Should return 200 with data structure
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
            'pagination'
        ]);
    }

    public function test_admin_orders_stats_route_exists()
    {
        $response = $this->getJson('/api/admin/orders/stats');
        
        // Route should exist (not return 404)
        $this->assertNotEquals(404, $response->status());
        
        // Should return 200 with stats structure
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'total_orders',
                'total_amount',
                'pending_orders',
                'paid_orders',
                'fulfilled_orders',
                'cancelled_orders'
            ]
        ]);
    }

    public function test_admin_middleware_works()
    {
        // Test without admin role should fail
        $regularUser = User::factory()->create();
        Sanctum::actingAs($regularUser);
        
        $response = $this->getJson('/api/admin/orders');
        $response->assertStatus(403);
        
        $response = $this->getJson('/api/admin/orders/stats');
        $response->assertStatus(403);
    }

    public function test_unauthenticated_access_denied()
    {
        // Test without authentication
        $response = $this->getJson('/api/admin/orders');
        $response->assertStatus(401);
        
        $response = $this->getJson('/api/admin/orders/stats');
        $response->assertStatus(401);
    }
}