<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Parcours critique pour l'event : un marchand s'inscrit, vérifie son compte
 * (lien email), puis publie un produit. Reproduit le chemin de production.
 */
class RegistrationAndPublicationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Rôles requis par l'attribution à l'inscription / isMerchant()
        Role::create(['name' => 'Business Enterprise', 'description' => 'Vendeur pro']);
        Role::create(['name' => 'Business Individual', 'description' => 'Vendeur individuel']);
        Role::create(['name' => 'Particulier', 'description' => 'Client']);

        // Une catégorie active pour la publication
        Category::create([
            'name' => 'Électronique',
            'slug' => 'electronique',
            'is_active' => true,
        ]);
    }

    public function test_merchant_can_register_verify_and_publish(): void
    {
        // 1. INSCRIPTION (marchand "Business")
        $register = $this->postJson('/api/auth/register', [
            'first_name' => 'Awa',
            'last_name' => 'Ndong',
            'email' => 'awa.ndong@example.com',
            'phone' => '077000111',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'Business',
            'company_name' => 'Boutique Awa',
        ]);

        $register->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.requires_verification', true);

        $token = $register->json('data.access_token');
        $this->assertNotEmpty($token, 'Un token doit être retourné à l\'inscription');

        $user = User::where('email', 'awa.ndong@example.com')->firstOrFail();
        $this->assertNull($user->verified_at, 'Le compte ne doit pas être vérifié avant la validation email');
        $this->assertTrue($user->isMerchant(), 'Un compte Business doit être marchand');

        // 2. PUBLICATION refusée tant que non vérifié
        $blocked = $this->withToken($token)->postJson('/api/products', [
            'name' => 'Téléphone',
            'description' => 'Un téléphone neuf',
            'price' => 150000,
            'category_id' => Category::first()->id,
            'sale_mode' => 'direct',
        ]);
        $blocked->assertStatus(403);

        // 3. VÉRIFICATION via le lien email (token base64 {email, expires_at})
        $verifToken = base64_encode(json_encode([
            'email' => $user->email,
            'expires_at' => now()->addHours(24)->toISOString(),
        ]));
        $verify = $this->getJson('/api/auth/verify-email/' . urlencode($verifToken));
        $verify->assertStatus(200)->assertJson(['success' => true]);

        $this->assertNotNull($user->fresh()->verified_at, 'Le compte doit être vérifié après le lien email');

        // Réinitialiser le guard : en prod chaque requête recharge l'utilisateur ;
        // en test le même process réutilise l'instance résolue précédemment.
        $this->app['auth']->forgetGuards();

        // 4. PUBLICATION acceptée une fois vérifié
        $published = $this->withToken($token)->postJson('/api/products', [
            'name' => 'Téléphone',
            'description' => 'Un téléphone neuf',
            'price' => 150000,
            'category_id' => Category::first()->id,
            'sale_mode' => 'direct',
        ]);

        $published->assertStatus(201);
        $this->assertDatabaseHas('products', [
            'name' => 'Téléphone',
            'merchant_id' => $user->id,
        ]);
    }
}
