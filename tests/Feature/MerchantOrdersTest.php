<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Payment;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;

class MerchantOrdersTest extends TestCase
{
    use DatabaseTransactions;

    protected User $merchant;
    protected User $otherMerchant;
    protected Product $product;
    protected Product $otherProduct;

    protected function setUp(): void
    {
        parent::setUp();

        // Create merchant user
        $this->merchant = User::create([
            'name' => 'Test Merchant',
            'email' => 'merchant@koumbaya.com',
            'phone' => '06123456',
            'password' => bcrypt('password'),
            'user_type_id' => 1, // Assuming merchant type
            'country_id' => 1,
            'language_id' => 1,
            'is_phone_verified' => true,
            'is_email_verified' => true,
        ]);

        // Create other merchant
        $this->otherMerchant = User::create([
            'name' => 'Other Merchant',
            'email' => 'other@koumbaya.com', 
            'phone' => '06654321',
            'password' => bcrypt('password'),
            'user_type_id' => 1,
            'country_id' => 1,
            'language_id' => 1,
            'is_phone_verified' => true,
            'is_email_verified' => true,
        ]);

        // Create products
        $this->product = Product::create([
            'name' => 'Test Product',
            'merchant_id' => $this->merchant->id,
            'price' => 5000,
            'is_active' => true,
        ]);

        $this->otherProduct = Product::create([
            'name' => 'Other Product',
            'merchant_id' => $this->otherMerchant->id,
            'price' => 3000,
            'is_active' => true,
        ]);
    }

    /**
     * Test merchant can access their orders
     */
    public function test_merchant_can_access_their_orders()
    {
        // Create orders for the merchant
        $order1 = Order::create([
            'user_id' => 1,
            'product_id' => $this->product->id,
            'order_number' => 'ORD-MERCHANT-001',
            'type' => 'lottery',
            'status' => OrderStatus::PAID->value,
            'total_amount' => 5000,
            'currency' => 'XAF',
            'paid_at' => now()
        ]);

        $order2 = Order::create([
            'user_id' => 1,
            'product_id' => $this->product->id,
            'order_number' => 'ORD-MERCHANT-002',
            'type' => 'direct',
            'status' => OrderStatus::PENDING->value,
            'total_amount' => 2500,
            'currency' => 'XAF'
        ]);

        // Create order for other merchant
        Order::create([
            'user_id' => 1,
            'product_id' => $this->otherProduct->id,
            'order_number' => 'ORD-OTHER-001',
            'type' => 'lottery', 
            'status' => OrderStatus::PAID->value,
            'total_amount' => 3000,
            'currency' => 'XAF'
        ]);

        // Mock merchant authentication
        Sanctum::actingAs($this->merchant);

        $response = $this->getJson('/api/merchant/orders');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ])
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'order_number',
                            'type',
                            'status',
                            'total_amount',
                            'currency',
                            'customer',
                            'product'
                        ]
                    ],
                    'pagination',
                    'summary'
                ]);

        // Check that only merchant's orders are returned
        $responseData = $response->json('data');
        $this->assertCount(2, $responseData);
        $this->assertTrue(collect($responseData)->every(fn($order) => 
            in_array($order['order_number'], ['ORD-MERCHANT-001', 'ORD-MERCHANT-002'])
        ));
    }

    /**
     * Test merchant orders filtering by status
     */
    public function test_merchant_orders_filtering_by_status()
    {
        // Create orders with different statuses
        Order::create([
            'user_id' => 1,
            'product_id' => $this->product->id,
            'order_number' => 'ORD-PAID-001',
            'type' => 'lottery',
            'status' => OrderStatus::PAID->value,
            'total_amount' => 5000,
            'currency' => 'XAF'
        ]);

        Order::create([
            'user_id' => 1,
            'product_id' => $this->product->id,
            'order_number' => 'ORD-PENDING-001',
            'type' => 'lottery',
            'status' => OrderStatus::PENDING->value,
            'total_amount' => 3000,
            'currency' => 'XAF'
        ]);

        Sanctum::actingAs($this->merchant);

        // Test filter by paid status
        $response = $this->getJson('/api/merchant/orders?status=paid');
        
        $response->assertStatus(200);
        $responseData = $response->json('data');
        $this->assertCount(1, $responseData);
        $this->assertEquals('paid', $responseData[0]['status']);

        // Test filter by pending status
        $response = $this->getJson('/api/merchant/orders?status=pending');
        
        $response->assertStatus(200);
        $responseData = $response->json('data');
        $this->assertCount(1, $responseData);
        $this->assertEquals('pending', $responseData[0]['status']);
    }

    /**
     * Test merchant orders filtering by date range
     */
    public function test_merchant_orders_filtering_by_date_range()
    {
        // Create orders at different dates
        $oldOrder = Order::create([
            'user_id' => 1,
            'product_id' => $this->product->id,
            'order_number' => 'ORD-OLD-001',
            'type' => 'lottery',
            'status' => OrderStatus::PAID->value,
            'total_amount' => 5000,
            'currency' => 'XAF',
            'created_at' => now()->subDays(30)
        ]);

        $recentOrder = Order::create([
            'user_id' => 1,
            'product_id' => $this->product->id,
            'order_number' => 'ORD-RECENT-001',
            'type' => 'lottery',
            'status' => OrderStatus::PAID->value,
            'total_amount' => 3000,
            'currency' => 'XAF',
            'created_at' => now()->subDays(5)
        ]);

        Sanctum::actingAs($this->merchant);

        // Test date range filter
        $dateFrom = now()->subDays(10)->format('Y-m-d');
        $dateTo = now()->format('Y-m-d');
        
        $response = $this->getJson("/api/merchant/orders?date_from={$dateFrom}&date_to={$dateTo}");
        
        $response->assertStatus(200);
        $responseData = $response->json('data');
        $this->assertCount(1, $responseData);
        $this->assertEquals('ORD-RECENT-001', $responseData[0]['order_number']);
    }

    /**
     * Test merchant orders CSV export
     */
    public function test_merchant_orders_csv_export()
    {
        // Create test orders
        Order::create([
            'user_id' => 1,
            'product_id' => $this->product->id,
            'order_number' => 'ORD-EXPORT-001',
            'type' => 'lottery',
            'status' => OrderStatus::PAID->value,
            'total_amount' => 5000,
            'currency' => 'XAF'
        ]);

        Order::create([
            'user_id' => 1,
            'product_id' => $this->product->id,
            'order_number' => 'ORD-EXPORT-002',
            'type' => 'direct',
            'status' => OrderStatus::PENDING->value,
            'total_amount' => 2500,
            'currency' => 'XAF'
        ]);

        Sanctum::actingAs($this->merchant);

        $response = $this->get('/api/merchant/orders/export');

        $response->assertStatus(200);
        $this->assertEquals('text/csv; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertStringContains('attachment; filename=', $response->headers->get('Content-Disposition'));
        
        // Check CSV content contains order data
        $content = $response->getContent();
        $this->assertStringContains('Numéro Commande', $content);
        $this->assertStringContains('ORD-EXPORT-001', $content);
        $this->assertStringContains('ORD-EXPORT-002', $content);
    }

    /**
     * Test merchant can get top products
     */
    public function test_merchant_can_get_top_products()
    {
        // Create orders to generate product statistics
        Order::create([
            'user_id' => 1,
            'product_id' => $this->product->id,
            'order_number' => 'ORD-TOP-001',
            'type' => 'lottery',
            'status' => OrderStatus::PAID->value,
            'total_amount' => 5000,
            'currency' => 'XAF'
        ]);

        Order::create([
            'user_id' => 1,
            'product_id' => $this->product->id,
            'order_number' => 'ORD-TOP-002',
            'type' => 'lottery',
            'status' => OrderStatus::FULFILLED->value,
            'total_amount' => 7500,
            'currency' => 'XAF'
        ]);

        Sanctum::actingAs($this->merchant);

        $response = $this->getJson('/api/merchant/orders/top-products');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ])
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'total_orders',
                            'total_revenue',
                            'avg_order_value'
                        ]
                    ]
                ]);

        $topProducts = $response->json('data');
        $this->assertNotEmpty($topProducts);
        $this->assertEquals($this->product->id, $topProducts[0]['id']);
        $this->assertEquals(2, $topProducts[0]['total_orders']);
        $this->assertEquals(12500, $topProducts[0]['total_revenue']);
    }

    /**
     * Test merchant orders pagination
     */
    public function test_merchant_orders_pagination()
    {
        // Create multiple orders
        for ($i = 1; $i <= 25; $i++) {
            Order::create([
                'user_id' => 1,
                'product_id' => $this->product->id,
                'order_number' => 'ORD-PAGE-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'type' => 'lottery',
                'status' => OrderStatus::PAID->value,
                'total_amount' => 1000 * $i,
                'currency' => 'XAF'
            ]);
        }

        Sanctum::actingAs($this->merchant);

        // Test first page
        $response = $this->getJson('/api/merchant/orders?per_page=10&page=1');
        
        $response->assertStatus(200);
        
        $pagination = $response->json('pagination');
        $this->assertEquals(1, $pagination['current_page']);
        $this->assertEquals(3, $pagination['last_page']); // 25 orders / 10 per page
        $this->assertEquals(10, $pagination['per_page']);
        $this->assertEquals(25, $pagination['total']);
        
        $this->assertCount(10, $response->json('data'));

        // Test second page
        $response = $this->getJson('/api/merchant/orders?per_page=10&page=2');
        
        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('pagination.current_page'));
        $this->assertCount(10, $response->json('data'));

        // Test last page
        $response = $this->getJson('/api/merchant/orders?per_page=10&page=3');
        
        $response->assertStatus(200);
        $this->assertEquals(3, $response->json('pagination.current_page'));
        $this->assertCount(5, $response->json('data')); // Remaining 5 orders
    }

    /**
     * Test non-merchant cannot access merchant orders
     */
    public function test_non_merchant_cannot_access_orders()
    {
        // Create regular customer user
        $customer = User::create([
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
            'user_type_id' => 2, // Customer type
            'country_id' => 1,
            'language_id' => 1,
        ]);

        Sanctum::actingAs($customer);

        $response = $this->getJson('/api/merchant/orders');
        
        $response->assertStatus(403); // Forbidden - middleware should block
    }

    /**
     * Test unauthenticated user cannot access merchant orders
     */
    public function test_unauthenticated_user_cannot_access_orders()
    {
        $response = $this->getJson('/api/merchant/orders');
        
        $response->assertStatus(401); // Unauthenticated
    }
}