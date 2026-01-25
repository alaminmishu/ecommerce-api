<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $enName = fake()->words(3, true);
        return [
            'uid' => 'P-' . strtoupper(Str::random(6)),
            'sku' => fake()->unique()->ean13(),
            'type' => 'simple',
            'en_name' => ucfirst($enName),
            'bn_name' => null,
            'slug' => Str::slug($enName),
            'en_description' => fake()->paragraphs(3, true),
            'bn_description' => null,
            'en_short_description' => fake()->sentence(),
            'bn_short_description' => null,
            'is_active' => true,
            'is_featured' => fake()->boolean(20),
            'is_new' => fake()->boolean(30),
            'sort_position' => fake()->numberBetween(0, 100),
            'published_at' => now(),

        ];
    }
}
