<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PaymentStatusMapper;
use App\Enums\PaymentStatus;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Log;

class PaymentStatusMapperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable logs during testing
        Log::shouldReceive('info')->andReturn(null);
        Log::shouldReceive('warning')->andReturn(null);
    }

    public function test_maps_success_statuses_to_paid()
    {
        $successStatuses = ['success', 'paid', 'completed', 'confirmed', 'successful', 'approved'];
        
        foreach ($successStatuses as $status) {
            $result = PaymentStatusMapper::fromGateway($status);
            $this->assertEquals(PaymentStatus::PAID, $result, "Status '{$status}' should map to PAID");
        }
    }

    public function test_maps_failure_statuses_to_failed()
    {
        $failureStatuses = ['failed', 'cancelled', 'rejected', 'error', 'declined', 'denied', 'invalid'];
        
        foreach ($failureStatuses as $status) {
            $result = PaymentStatusMapper::fromGateway($status);
            $this->assertEquals(PaymentStatus::FAILED, $result, "Status '{$status}' should map to FAILED");
        }
    }

    public function test_maps_pending_statuses_to_pending()
    {
        $pendingStatuses = ['pending', 'processing', 'initiated', 'waiting', 'in_progress'];
        
        foreach ($pendingStatuses as $status) {
            $result = PaymentStatusMapper::fromGateway($status);
            $this->assertEquals(PaymentStatus::PENDING, $result, "Status '{$status}' should map to PENDING");
        }
    }

    public function test_maps_expired_statuses_to_expired()
    {
        $expiredStatuses = ['expired', 'timeout', 'timed_out'];
        
        foreach ($expiredStatuses as $status) {
            $result = PaymentStatusMapper::fromGateway($status);
            $this->assertEquals(PaymentStatus::EXPIRED, $result, "Status '{$status}' should map to EXPIRED");
        }
    }

    public function test_maps_unknown_status_to_pending()
    {
        $unknownStatuses = ['unknown', 'weird_status', '', 'null'];
        
        foreach ($unknownStatuses as $status) {
            $result = PaymentStatusMapper::fromGateway($status);
            $this->assertEquals(PaymentStatus::PENDING, $result, "Unknown status '{$status}' should default to PENDING");
        }
    }

    public function test_case_insensitive_mapping()
    {
        $this->assertEquals(PaymentStatus::PAID, PaymentStatusMapper::fromGateway('SUCCESS'));
        $this->assertEquals(PaymentStatus::PAID, PaymentStatusMapper::fromGateway('Success'));
        $this->assertEquals(PaymentStatus::PAID, PaymentStatusMapper::fromGateway('PAID'));
        $this->assertEquals(PaymentStatus::FAILED, PaymentStatusMapper::fromGateway('FAILED'));
        $this->assertEquals(PaymentStatus::PENDING, PaymentStatusMapper::fromGateway('PENDING'));
    }

    public function test_payment_to_order_status_mapping()
    {
        $this->assertEquals(OrderStatus::PAID, PaymentStatusMapper::toOrderStatus(PaymentStatus::PAID));
        $this->assertEquals(OrderStatus::FAILED, PaymentStatusMapper::toOrderStatus(PaymentStatus::FAILED));
        $this->assertEquals(OrderStatus::FAILED, PaymentStatusMapper::toOrderStatus(PaymentStatus::EXPIRED));
        $this->assertEquals(OrderStatus::AWAITING_PAYMENT, PaymentStatusMapper::toOrderStatus(PaymentStatus::PENDING));
    }

    public function test_valid_payment_status_transitions()
    {
        // Valid transitions from pending
        $this->assertTrue(PaymentStatusMapper::isValidTransition(PaymentStatus::PENDING, PaymentStatus::PAID));
        $this->assertTrue(PaymentStatusMapper::isValidTransition(PaymentStatus::PENDING, PaymentStatus::FAILED));
        $this->assertTrue(PaymentStatusMapper::isValidTransition(PaymentStatus::PENDING, PaymentStatus::EXPIRED));

        // Invalid transitions from final states
        $this->assertFalse(PaymentStatusMapper::isValidTransition(PaymentStatus::PAID, PaymentStatus::PENDING));
        $this->assertFalse(PaymentStatusMapper::isValidTransition(PaymentStatus::FAILED, PaymentStatus::PAID));
        $this->assertFalse(PaymentStatusMapper::isValidTransition(PaymentStatus::EXPIRED, PaymentStatus::PENDING));
    }

    public function test_valid_order_status_transitions()
    {
        // Valid transitions from pending
        $this->assertTrue(PaymentStatusMapper::isValidOrderTransition(OrderStatus::PENDING, OrderStatus::AWAITING_PAYMENT));
        $this->assertTrue(PaymentStatusMapper::isValidOrderTransition(OrderStatus::PENDING, OrderStatus::CANCELLED));

        // Valid transitions from awaiting payment
        $this->assertTrue(PaymentStatusMapper::isValidOrderTransition(OrderStatus::AWAITING_PAYMENT, OrderStatus::PAID));
        $this->assertTrue(PaymentStatusMapper::isValidOrderTransition(OrderStatus::AWAITING_PAYMENT, OrderStatus::FAILED));

        // Valid transitions from paid
        $this->assertTrue(PaymentStatusMapper::isValidOrderTransition(OrderStatus::PAID, OrderStatus::FULFILLED));
        $this->assertTrue(PaymentStatusMapper::isValidOrderTransition(OrderStatus::PAID, OrderStatus::REFUNDED));

        // Invalid transitions
        $this->assertFalse(PaymentStatusMapper::isValidOrderTransition(OrderStatus::FAILED, OrderStatus::PAID));
        $this->assertFalse(PaymentStatusMapper::isValidOrderTransition(OrderStatus::REFUNDED, OrderStatus::PAID));
    }

    public function test_contextual_status_messages()
    {
        $message1 = PaymentStatusMapper::getStatusMessage(PaymentStatus::PAID, OrderStatus::PAID);
        $this->assertStringContains('Paiement confirmé - Commande en cours', $message1);

        $message2 = PaymentStatusMapper::getStatusMessage(PaymentStatus::PAID, OrderStatus::FULFILLED);
        $this->assertStringContains('Commande livrée avec succès', $message2);

        $message3 = PaymentStatusMapper::getStatusMessage(PaymentStatus::FAILED, OrderStatus::FAILED);
        $this->assertStringContains('Commande annulée', $message3);

        // Without order context
        $message4 = PaymentStatusMapper::getStatusMessage(PaymentStatus::PENDING);
        $this->assertStringContains('Paiement en cours', $message4);
    }

    public function test_enum_helper_methods()
    {
        // PaymentStatus helpers
        $this->assertTrue(PaymentStatus::PAID->isFinal());
        $this->assertTrue(PaymentStatus::FAILED->isFinal());
        $this->assertTrue(PaymentStatus::EXPIRED->isFinal());
        $this->assertFalse(PaymentStatus::PENDING->isFinal());

        $this->assertTrue(PaymentStatus::PAID->isSuccessful());
        $this->assertFalse(PaymentStatus::FAILED->isSuccessful());

        $this->assertTrue(PaymentStatus::PENDING->isProcessable());
        $this->assertFalse(PaymentStatus::PAID->isProcessable());

        // OrderStatus helpers
        $this->assertTrue(OrderStatus::PAID->isFinal());
        $this->assertTrue(OrderStatus::FULFILLED->isFinal());
        $this->assertFalse(OrderStatus::PENDING->isFinal());

        $this->assertTrue(OrderStatus::PAID->isSuccessful());
        $this->assertTrue(OrderStatus::FULFILLED->isSuccessful());
        $this->assertFalse(OrderStatus::FAILED->isSuccessful());

        $this->assertTrue(OrderStatus::PENDING->canBeCancelled());
        $this->assertTrue(OrderStatus::AWAITING_PAYMENT->canBeCancelled());
        $this->assertFalse(OrderStatus::PAID->canBeCancelled());

        $this->assertTrue(OrderStatus::PAID->canBeFulfilled());
        $this->assertFalse(OrderStatus::PENDING->canBeFulfilled());
    }

    public function test_gateway_mappings_retrieval()
    {
        $mappings = PaymentStatusMapper::getGatewayMappings();
        
        $this->assertIsArray($mappings);
        $this->assertArrayHasKey('success', $mappings);
        $this->assertArrayHasKey('failed', $mappings);
        $this->assertArrayHasKey('pending', $mappings);
        $this->assertArrayHasKey('expired', $mappings);
        
        $this->assertEquals('paid', $mappings['success']);
        $this->assertEquals('failed', $mappings['failed']);
        $this->assertEquals('pending', $mappings['pending']);
        $this->assertEquals('expired', $mappings['expired']);
    }

    public function test_custom_gateway_mapping()
    {
        // Add custom mapping
        PaymentStatusMapper::addGatewayMapping('custom_success', PaymentStatus::PAID);
        
        // Test the custom mapping
        $result = PaymentStatusMapper::fromGateway('custom_success');
        $this->assertEquals(PaymentStatus::PAID, $result);
    }
}