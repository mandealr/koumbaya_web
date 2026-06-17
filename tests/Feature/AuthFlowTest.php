<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'Business Enterprise', 'description' => 'Vendeur pro']);
        Role::create(['name' => 'Business Individual', 'description' => 'Vendeur individuel']);
        Role::create(['name' => 'Particulier', 'description' => 'Client']);
    }

    public function test_customer_can_register(): void
    {
        $res = $this->postJson('/api/auth/register', [
            'first_name' => 'Koffi', 'last_name' => 'Mba',
            'email' => 'koffi@example.com', 'phone' => '077100200',
            'password' => 'secret123', 'password_confirmation' => 'secret123',
        ]);

        $res->assertStatus(201)->assertJson(['success' => true]);
        $this->assertNotEmpty($res->json('data.access_token'));
        $user = User::where('email', 'koffi@example.com')->firstOrFail();
        $this->assertFalse($user->isMerchant());
    }

    public function test_business_registration_creates_a_merchant(): void
    {
        $res = $this->postJson('/api/auth/register', [
            'first_name' => 'Awa', 'last_name' => 'Ndong',
            'email' => 'awa@example.com', 'phone' => '077100201',
            'password' => 'secret123', 'password_confirmation' => 'secret123',
            'role' => 'Business', 'company_name' => 'Awa SARL',
        ]);

        $res->assertStatus(201);
        $this->assertTrue(User::where('email', 'awa@example.com')->firstOrFail()->isMerchant());
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'dup@example.com']);

        $this->postJson('/api/auth/register', [
            'first_name' => 'X', 'last_name' => 'Y',
            'email' => 'dup@example.com', 'phone' => '077100299',
            'password' => 'secret123', 'password_confirmation' => 'secret123',
        ])->assertStatus(422);
    }

    public function test_login_succeeds_with_valid_credentials(): void
    {
        User::factory()->create(['email' => 'log@example.com', 'password' => Hash::make('secret123')]);

        $res = $this->postJson('/api/auth/login', [
            'email' => 'log@example.com', 'password' => 'secret123',
        ]);

        $res->assertStatus(200)->assertJson(['success' => true]);
        $this->assertNotEmpty($res->json('data.access_token'));
    }

    public function test_login_fails_with_invalid_password(): void
    {
        User::factory()->create(['email' => 'log2@example.com', 'password' => Hash::make('secret123')]);

        $this->postJson('/api/auth/login', [
            'email' => 'log2@example.com', 'password' => 'wrongpass',
        ])->assertStatus(401);
    }

    public function test_login_is_rate_limited(): void
    {
        User::factory()->create(['email' => 'rl@example.com', 'password' => Hash::make('secret123')]);

        $statuses = [];
        for ($i = 0; $i < 13; $i++) {
            $statuses[] = $this->postJson('/api/auth/login', [
                'email' => 'rl@example.com', 'password' => 'wrong',
            ])->getStatusCode();
        }

        $this->assertContains(429, $statuses, 'Le login doit être limité (throttle) après plusieurs tentatives');
    }

    public function test_authenticated_user_can_fetch_profile_and_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createAuthToken('test-token');

        $this->withToken($token)->getJson('/api/auth/me')->assertStatus(200);
        $this->withToken($token)->postJson('/api/auth/logout')->assertStatus(200);
    }
}
