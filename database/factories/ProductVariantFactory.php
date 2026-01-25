<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => fake()->unique()->ean13(),
            'barcode' => fake()->ean13(),
            'price' => fake()->randomFloat(2, 100, 50000),
            'compare_price' => fake()->optional()->randomFloat(2, 150, 60000),
            'cost' => fake()->randomFloat(2, 50, 40000),
            'stock' => fake()->numberBetween(0, 100),
            'low_stock_threshold' => 5,
            'weight' => fake()->randomFloat(2, 0.1, 50),
            'is_default' => true,
            'is_active' => true,
            'sort_position' => 0,
        ];
    }
}
