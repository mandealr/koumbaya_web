<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $saleMode = $this->faker->randomElement(['lottery', 'direct']);
        
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 500, 10000),
            'currency' => 'XAF',
            'sale_mode' => $saleMode,
            'stock' => $saleMode === 'direct' ? $this->faker->numberBetween(0, 100) : null,
            'image' => $this->faker->optional()->imageUrl(400, 300, 'products'),
            'is_active' => $this->faker->boolean(85),
            'featured' => $this->faker->boolean(20),
            'category_id' => 1,
            'meta' => $this->faker->optional(0.3)->randomElement([
                ['weight' => '1kg', 'dimensions' => '20x15x5'],
                ['color' => 'blue', 'material' => 'cotton'],
                ['brand' => 'TestBrand', 'warranty' => '1 year'],
            ]),
        ];
    }

    /**
     * Create a lottery product
     */
    public function lottery(): static
    {
        return $this->state(fn (array $attributes) => [
            'sale_mode' => 'lottery',
            'stock' => null,
        ]);
    }

    /**
     * Create a direct sale product
     */
    public function direct(): static
    {
        return $this->state(fn (array $attributes) => [
            'sale_mode' => 'direct',
            'stock' => $this->faker->numberBetween(1, 100),
        ]);
    }

    /**
     * Create an active product
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Create an inactive product
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}