<?php

namespace Tests\Feature;

use App\Models\Lottery;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Vérifie que les Policies d'ownership bloquent bien l'accès croisé (anti-IDOR).
 */
class OwnershipPolicyTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(User $merchant): Product
    {
        return Product::create([
            'name' => 'Produit',
            'description' => 'desc',
            'price' => 50000,
            'currency' => 'XAF',
            'category_id' => 1,
            'merchant_id' => $merchant->id,
            'sale_mode' => 'lottery',
        ]);
    }

    public function test_owner_can_update_product_but_stranger_cannot(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $product = $this->makeProduct($owner);

        $this->assertTrue($owner->can('update', $product));
        $this->assertFalse($stranger->can('update', $product));
        $this->assertTrue($owner->can('createLottery', $product));
        $this->assertFalse($stranger->can('createLottery', $product));
    }

    public function test_only_product_owner_can_draw_its_lottery(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $product = $this->makeProduct($owner);

        $lottery = Lottery::create([
            'lottery_number' => 'LOT-POL-' . uniqid(),
            'title' => 'T',
            'description' => 'd',
            'product_id' => $product->id,
            'ticket_price' => 1000,
            'currency' => 'XAF',
            'max_tickets' => 10,
            'sold_tickets' => 0,
            'draw_date' => now()->addDay(),
            'status' => 'active',
        ]);
        $lottery->setRelation('product', $product);

        $this->assertTrue($owner->can('draw', $lottery));
        $this->assertFalse($stranger->can('draw', $lottery));
    }
}
