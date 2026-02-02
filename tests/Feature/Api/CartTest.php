<?php

namespace Tests\Feature\Api;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private string $sessionId = 'test-session-123';

    public function test_can_get_empty_cart(): void
    {
        $response = $this->getJson('/api/v1/cart', [
            'X-Cart-Session' => $this->sessionId,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'total' => 0,
                'item_count' => 0,
            ]);
    }

    public function test_can_add_item_to_cart(): void
    {
        $product = Product::factory()
            ->has(ProductVariant::factory()->state([
                'price' => 1000,
                'stock' => 10,
            ]), 'variants')
            ->create(['is_active' => true]);

        $variant = $product->variants->first();

        $response = $this->postJson('/api/v1/cart/items', [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ], [
            'X-Cart-Session' => $this->sessionId,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Item added to cart',
            ]);
    }

    public function test_cart_calculates_total_correctly(): void
    {
        $product = Product::factory()
            ->has(ProductVariant::factory()->state([
                'price' => 1000,
                'stock' => 10,
            ]), 'variants')
            ->create(['is_active' => true]);

        $variant = $product->variants->first();

        // Add item
        $this->postJson('/api/v1/cart/items', [
            'product_variant_id' => $variant->id,
            'quantity' => 3,
        ], [
            'X-Cart-Session' => $this->sessionId,
        ]);

        // Get cart
        $response = $this->getJson('/api/v1/cart', [
            'X-Cart-Session' => $this->sessionId,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'total' => 3000, // 3 * 1000
                'item_count' => 3,
            ]);
    }

    public function test_cannot_add_item_with_insufficient_stock(): void
    {
        $product = Product::factory()
            ->has(ProductVariant::factory()->state([
                'price' => 1000,
                'stock' => 2, // Only 2 in stock
            ]), 'variants')
            ->create(['is_active' => true]);

        $variant = $product->variants->first();

        $response = $this->postJson('/api/v1/cart/items', [
            'product_variant_id' => $variant->id,
            'quantity' => 5, // Trying to add 5
        ], [
            'X-Cart-Session' => $this->sessionId,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Insufficient stock',
            ]);
    }

    public function test_can_update_cart_item_quantity(): void
    {
        $product = Product::factory()
            ->has(ProductVariant::factory()->state([
                'price' => 1000,
                'stock' => 10,
            ]), 'variants')
            ->create(['is_active' => true]);

        $variant = $product->variants->first();

        // Add item
        $this->postJson('/api/v1/cart/items', [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ], [
            'X-Cart-Session' => $this->sessionId,
        ]);

        // Get cart to find item ID
        $cart = Cart::where('session_id', $this->sessionId)->first();
        $itemId = $cart->items->first()->id;

        // Update quantity
        $response = $this->putJson("/api/v1/cart/items/{$itemId}", [
            'quantity' => 5,
        ], [
            'X-Cart-Session' => $this->sessionId,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Cart updated',
            ]);
    }

    public function test_can_remove_item_from_cart(): void
    {
        $product = Product::factory()
            ->has(ProductVariant::factory()->state([
                'price' => 1000,
                'stock' => 10,
            ]), 'variants')
            ->create(['is_active' => true]);

        $variant = $product->variants->first();

        // Add item
        $this->postJson('/api/v1/cart/items', [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ], [
            'X-Cart-Session' => $this->sessionId,
        ]);

        // Get cart to find item ID
        $cart = Cart::where('session_id', $this->sessionId)->first();
        $itemId = $cart->items->first()->id;

        // Remove item
        $response = $this->deleteJson("/api/v1/cart/items/{$itemId}", [], [
            'X-Cart-Session' => $this->sessionId,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Item removed from cart',
            ]);

        // Verify cart is empty
        $cart->refresh();
        $this->assertEquals(0, $cart->items->count());
    }

    public function test_can_clear_cart(): void
    {
        $product = Product::factory()
            ->has(ProductVariant::factory()->state([
                'price' => 1000,
                'stock' => 10,
            ]), 'variants')
            ->create(['is_active' => true]);

        $variant = $product->variants->first();

        // Add items
        $this->postJson('/api/v1/cart/items', [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ], [
            'X-Cart-Session' => $this->sessionId,
        ]);

        // Clear cart
        $response = $this->deleteJson('/api/v1/cart', [], [
            'X-Cart-Session' => $this->sessionId,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Cart cleared',
            ]);
    }
}
