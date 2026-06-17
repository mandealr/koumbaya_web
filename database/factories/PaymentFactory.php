<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reference' => 'KMB-PAY-' . strtoupper(\Illuminate\Support\Str::random(16)),
            'user_id' => User::factory(),
            'order_id' => Order::factory(),
            'amount' => $this->faker->randomFloat(2, 500, 50000),
            'currency' => 'XAF',
            'status' => 'pending',
            'payment_method' => $this->faker->randomElement(['airtel_money', 'moov_money']),
            'ebilling_id' => 'EB-' . $this->faker->unique()->numerify('########'),
            'transaction_id' => null,
            'callback_data' => null,
            'paid_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
            'paid_at' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => 'paid',
            'paid_at' => now(),
            'transaction_id' => 'TXN-' . strtoupper(uniqid()),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => ['status' => 'failed']);
    }
}
