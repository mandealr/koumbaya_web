<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $type = $this->faker->randomElement(['lottery', 'direct']);

        return [
            'order_number' => 'ORD-' . strtoupper(\Illuminate\Support\Str::random(16)),
            'user_id' => User::factory(),
            'type' => $type,
            'product_id' => null,
            'lottery_id' => null,
            'total_amount' => $this->faker->randomFloat(2, 1000, 50000),
            'currency' => 'XAF',
            'status' => 'pending',
            'payment_reference' => null,
            'paid_at' => null,
            'fulfilled_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
            'paid_at' => null,
            'payment_reference' => null,
            'fulfilled_at' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function awaitingPayment(): static
    {
        return $this->state(fn () => ['status' => 'awaiting_payment']);
    }
}
