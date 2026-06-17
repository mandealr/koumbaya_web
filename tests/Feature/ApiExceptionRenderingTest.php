<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Vérifie le contrat d'erreur API homogène rendu par ApiExceptionRenderer.
 */
class ApiExceptionRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_api_route_returns_standardized_json_404(): void
    {
        $response = $this->getJson('/api/route-qui-nexiste-pas');

        $response->assertStatus(404)
            ->assertJson(['success' => false])
            ->assertJsonStructure(['success', 'message']);
    }

    public function test_protected_route_without_token_returns_standardized_json_401(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401)
            ->assertJson(['success' => false])
            ->assertJsonStructure(['success', 'message']);
    }
}
