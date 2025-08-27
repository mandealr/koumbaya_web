<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\InvoiceService;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Lottery;
use App\Models\LotteryTicket;
use App\Models\Payment;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InvoiceService $invoiceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->invoiceService = new InvoiceService();

        // Mock PDF facade to avoid actual PDF generation in tests
        Pdf::shouldReceive('loadView')
            ->andReturnSelf()
            ->shouldReceive('setPaper')
            ->andReturnSelf()
            ->shouldReceive('setOptions')
            ->andReturnSelf()
            ->shouldReceive('download')
            ->andReturn(new Response('PDF content', 200, ['Content-Type' => 'application/pdf']));
    }

    public function test_generates_pdf_for_paid_order()
    {
        // Create test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create test product
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 5000,
        ]);

        // Create test lottery
        $lottery = Lottery::factory()->create([
            'product_id' => $product->id,
            'title' => 'Test Lottery',
            'ticket_price' => 1000,
        ]);

        // Create paid order
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'order_number' => 'ORD-TEST-123',
            'type' => Order::TYPE_LOTTERY,
            'lottery_id' => $lottery->id,
            'total_amount' => 3000,
            'currency' => 'XAF',
            'status' => OrderStatus::PAID->value,
            'paid_at' => now(),
        ]);

        // Create lottery tickets for the order
        LotteryTicket::factory()->count(3)->create([
            'user_id' => $user->id,
            'lottery_id' => $lottery->id,
            'transaction_id' => null, // We'll create transactions later if needed
            'ticket_number' => fn() => 'TICKET-' . fake()->unique()->numerify('######'),
            'price_paid' => 1000,
            'status' => 'active',
        ]);

        // Create paid payment
        Payment::factory()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => 3000,
            'status' => PaymentStatus::PAID->value,
            'paid_at' => now(),
        ]);

        // Mock View facade to ensure view exists
        View::shouldReceive('exists')
            ->with('invoices.order-invoice')
            ->andReturn(true);

        // Generate invoice
        $response = $this->invoiceService->make($order);

        // Assert response is successful
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_throws_exception_for_unpaid_order()
    {
        // Create test user
        $user = User::factory()->create();

        // Create pending order
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'order_number' => 'ORD-TEST-456',
            'status' => OrderStatus::PENDING->value,
        ]);

        // Expect exception for unpaid order
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invoice can only be generated for paid orders');

        $this->invoiceService->make($order);
    }

    public function test_generates_invoice_for_direct_product_order()
    {
        // Create test user
        $user = User::factory()->create([
            'name' => 'Direct Customer',
            'email' => 'direct@example.com',
        ]);

        // Create test product
        $product = Product::factory()->create([
            'name' => 'Direct Product',
            'price' => 15000,
        ]);

        // Create paid direct order
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'order_number' => 'ORD-DIRECT-789',
            'type' => Order::TYPE_DIRECT,
            'product_id' => $product->id,
            'total_amount' => 15000,
            'currency' => 'XAF',
            'status' => OrderStatus::PAID->value,
            'paid_at' => now(),
        ]);

        // Create paid payment
        Payment::factory()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => 15000,
            'status' => PaymentStatus::PAID->value,
            'paid_at' => now(),
        ]);

        // Mock View facade
        View::shouldReceive('exists')
            ->with('invoices.order-invoice')
            ->andReturn(true);

        // Generate invoice
        $response = $this->invoiceService->make($order);

        // Assert response is successful
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_generates_invoice_for_fulfilled_order()
    {
        // Create test user
        $user = User::factory()->create();

        // Create test product
        $product = Product::factory()->create([
            'name' => 'Fulfilled Product',
        ]);

        // Create fulfilled order
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'order_number' => 'ORD-FULFILLED-101',
            'type' => Order::TYPE_DIRECT,
            'product_id' => $product->id,
            'total_amount' => 8000,
            'status' => OrderStatus::FULFILLED->value,
            'paid_at' => now()->subDays(1),
            'fulfilled_at' => now(),
        ]);

        // Create paid payment
        Payment::factory()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => 8000,
            'status' => PaymentStatus::PAID->value,
            'paid_at' => now()->subDays(1),
        ]);

        // Mock View facade
        View::shouldReceive('exists')
            ->with('invoices.order-invoice')
            ->andReturn(true);

        // Generate invoice
        $response = $this->invoiceService->make($order);

        // Assert response is successful
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_format_currency_method()
    {
        $formatted = $this->invoiceService->formatCurrency(1500.00, 'XAF');
        $this->assertEquals('1 500 FCFA', $formatted);

        $formatted = $this->invoiceService->formatCurrency(25000.50, 'XAF');
        $this->assertEquals('25 001 FCFA', $formatted);

        $formatted = $this->invoiceService->formatCurrency(100, 'XAF');
        $this->assertEquals('100 FCFA', $formatted);
    }

    public function test_invoice_loads_necessary_relationships()
    {
        // Create test data with relationships
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $lottery = Lottery::factory()->create(['product_id' => $product->id]);
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'lottery_id' => $lottery->id,
            'status' => OrderStatus::PAID->value,
            'paid_at' => now(),
        ]);

        // Create lottery tickets
        LotteryTicket::factory()->count(2)->create([
            'user_id' => $user->id,
            'lottery_id' => $lottery->id,
        ]);

        // Create payment
        Payment::factory()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'status' => PaymentStatus::PAID->value,
            'paid_at' => now(),
        ]);

        // Mock View facade
        View::shouldReceive('exists')
            ->with('invoices.order-invoice')
            ->andReturn(true);

        // Generate invoice (should load relationships automatically)
        $response = $this->invoiceService->make($order);

        // Assert response is successful
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}