<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function verifiedMerchant(): array
    {
        $role = Role::firstOrCreate(['name' => 'Business Enterprise'], ['description' => 'pro']);
        $user = User::factory()->create(['verified_at' => now(), 'user_type_id' => 1]);
        $user->roles()->attach($role->id);

        return [$user, $user->createAuthToken('t')];
    }

    public function test_public_can_list_products(): void
    {
        Product::factory()->count(3)->create();

        $this->getJson('/api/products')->assertStatus(200);
    }

    public function test_public_can_view_a_product(): void
    {
        $product = Product::factory()->create();

        $this->getJson('/api/products/' . $product->id)->assertStatus(200);
    }

    public function test_verified_merchant_can_publish_direct_product(): void
    {
        [$merchant, $token] = $this->verifiedMerchant();
        $category = Category::factory()->create();

        $res = $this->withToken($token)->postJson('/api/products', [
            'name' => 'Ordinateur portable',
            'description' => 'Un PC neuf sous garantie',
            'price' => 250000,
            'category_id' => $category->id,
            'sale_mode' => 'direct',
        ]);

        $res->assertStatus(201);
        $this->assertDatabaseHas('products', [
            'name' => 'Ordinateur portable',
            'merchant_id' => $merchant->id,
        ]);
    }

    public function test_customer_cannot_publish_product(): void
    {
        $customer = User::factory()->create(['verified_at' => now()]);
        $token = $customer->createAuthToken('t');
        $category = Category::factory()->create();

        $this->withToken($token)->postJson('/api/products', [
            'name' => 'Tentative', 'description' => 'desc', 'price' => 5000,
            'category_id' => $category->id, 'sale_mode' => 'direct',
        ])->assertStatus(403);
    }

    public function test_owner_can_update_but_stranger_cannot(): void
    {
        [$merchant, $token] = $this->verifiedMerchant();
        $product = Product::factory()->create(['merchant_id' => $merchant->id]);

        $this->withToken($token)
            ->putJson('/api/products/' . $product->id, ['name' => 'Nouveau nom'])
            ->assertStatus(200);

        // En prod chaque requête recharge l'utilisateur ; en test le guard est
        // mis en cache entre deux appels du même process.
        $this->app['auth']->forgetGuards();

        [, $otherToken] = $this->verifiedMerchant();
        $this->withToken($otherToken)
            ->putJson('/api/products/' . $product->id, ['name' => 'Pirate'])
            ->assertStatus(403);
    }
}
