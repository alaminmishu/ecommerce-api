<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products(): void
    {
        // Create test products
        Product::factory()
            ->has(ProductVariant::factory(), 'variants')
            ->count(5)
            ->create(['is_active' => true, 'published_at' => now()]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'uid',
                        'name',
                        'slug',
                        'price',
                        'stock',
                        'inStock',
                    ]
                ],
                'meta'
            ]);
    }

    public function test_can_get_single_product(): void
    {
        $product = Product::factory()
            ->has(ProductVariant::factory(), 'variants')
            ->create(['is_active' => true, 'published_at' => now()]);

        $response = $this->getJson("/api/v1/products/{$product->uid}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'uid' => $product->uid,
                    'name' => $product->en_name,
                ]
            ]);
    }

    public function test_cannot_get_inactive_product(): void
    {
        $product = Product::factory()
            ->create(['is_active' => false]);

        $response = $this->getJson("/api/v1/products/{$product->uid}");

        $response->assertStatus(404);
    }

    public function test_products_have_price_information(): void
    {
        $product = Product::factory()
            ->has(ProductVariant::factory()->state([
                'price' => 1000,
                'compare_price' => 1200,
            ]), 'variants')
            ->create(['is_active' => true, 'published_at' => now()]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'regular' => 1000.0,
                'compare' => 1200.0,
                'discount' => 17, // (1200-1000)/1200 * 100
            ]);
    }
}
