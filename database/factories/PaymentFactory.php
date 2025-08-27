<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending', 'paid', 'failed', 'expired']);
        
        return [
            'reference' => 'KMB-PAY-' . strtoupper(uniqid()),
            'user_id' => User::factory(),
            'order_id' => Order::factory(),
            'amount' => $this->faker->randomFloat(2, 500, 50000),
            'currency' => 'XAF',
            'status' => $status,
            'payment_method' => $this->faker->randomElement(['airtel_money', 'moov_money']),
            'payment_gateway' => 'ebilling',
            'ebilling_id' => 'EB-' . $this->faker->unique()->numerify('########'),
            'external_transaction_id' => $status === 'paid' ? 'TXN-' . strtoupper(uniqid()) : null,
            'gateway_response' => $this->faker->optional(0.7)->randomElement([
                ['status' => 'success', 'message' => 'Payment successful'],
                ['status' => 'pending', 'message' => 'Payment in progress'],
                ['status' => 'failed', 'error' => 'Insufficient funds'],
            ]),
            'callback_data' => $this->faker->optional(0.5)->randomElement([
                [
                    'gateway' => 'airtelmoney',
                    'timestamp' => now()->toISOString(),
                    'ip' => $this->faker->ipv4()
                ],
                [
                    'gateway' => 'moovmoney',
                    'timestamp' => now()->toISOString(),
                    'signature_valid' => true
                ]
            ]),
            'paid_at' => $status === 'paid' ? $this->faker->dateTimeBetween('-30 days', 'now') : null,
            'timeout_at' => $status === 'pending' ? $this->faker->dateTimeBetween('now', '+1 hour') : null,
        ];
    }

    /**
     * Create a paid payment
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'external_transaction_id' => 'TXN-' . strtoupper(uniqid()),
            'gateway_response' => ['status' => 'success', 'message' => 'Payment successful']
        ]);
    }

    /**
     * Create a pending payment
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_at' => null,
            'external_transaction_id' => null,
            'timeout_at' => $this->faker->dateTimeBetween('now', '+1 hour'),
            'gateway_response' => ['status' => 'pending', 'message' => 'Payment in progress']
        ]);
    }

    /**
     * Create a failed payment
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'paid_at' => null,
            'external_transaction_id' => null,
            'timeout_at' => null,
            'gateway_response' => ['status' => 'failed', 'error' => 'Payment failed']
        ]);
    }

    /**
     * Create an expired payment
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'paid_at' => null,
            'external_transaction_id' => null,
            'timeout_at' => $this->faker->dateTimeBetween('-2 hours', '-5 minutes'),
            'gateway_response' => ['status' => 'expired', 'message' => 'Payment expired']
        ]);
    }
}