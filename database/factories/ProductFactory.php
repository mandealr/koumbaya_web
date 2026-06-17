<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->words(3, true)),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 1000, 100000),
            'currency' => 'XAF',
            'category_id' => Category::factory(),
            'merchant_id' => User::factory(),
            'sale_mode' => $this->faker->randomElement(['lottery', 'direct']),
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'is_active' => true,
            'is_featured' => false,
        ];
    }

    public function lottery(): static
    {
        return $this->state(fn () => ['sale_mode' => 'lottery']);
    }

    public function direct(): static
    {
        return $this->state(fn () => ['sale_mode' => 'direct']);
    }
}
