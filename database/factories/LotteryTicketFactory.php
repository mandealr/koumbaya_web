<?php

namespace Database\Factories;

use App\Models\Lottery;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LotteryTicket>
 */
class LotteryTicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ticket_number' => 'TKT-' . strtoupper(uniqid()),
            'lottery_id' => Lottery::factory(),
            'user_id' => User::factory(),
            'price' => 1000,
            'currency' => 'XAF',
            'status' => 'paid',
            'is_winner' => false,
            'purchased_at' => now(),
        ];
    }

    public function reserved(): static
    {
        return $this->state(fn () => ['status' => 'reserved', 'purchased_at' => null]);
    }

    public function winner(): static
    {
        return $this->state(fn () => ['status' => 'paid', 'is_winner' => true]);
    }
}
