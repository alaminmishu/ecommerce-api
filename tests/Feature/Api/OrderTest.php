<?php

namespace Tests\Feature\Api;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private string $sessionId = 'test-order-session';

    private function addItemToCart(): ProductVariant
    {
        $product = Product::factory()
            ->has(ProductVariant::factory()->state([
                'price' => 1000,
                'stock' => 10,
            ]), 'variants')
            ->create(['is_active' => true]);

        $variant = $product->variants->first();

        $this->postJson('/api/v1/cart/items', [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ], [
            'X-Cart-Session' => $this->sessionId,
        ]);

        return $variant;
    }

    public function test_can_checkout_as_guest(): void
    {
        $this->addItemToCart();

        $response = $this->postJson('/api/v1/checkout', [
            'shipping_name' => 'Test User',
            'shipping_email' => 'test@example.com',
            'shipping_phone' => '+8801712345678',
            'shipping_address' => '123 Test Street',
            'shipping_city' => 'Dhaka',
            'shipping_zip' => '1205',
            'payment_method' => 'cash_on_delivery',
        ], [
            'X-Cart-Session' => $this->sessionId,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'order' => [
                    'id',
                    'order_number',
                    'total',
                ],
            ]);
    }

    public function test_order_reduces_stock(): void
    {
        $variant = $this->addItemToCart();
        $originalStock = $variant->stock;

        $this->postJson('/api/v1/checkout', [
            'shipping_name' => 'Test User',
            'shipping_email' => 'test@example.com',
            'shipping_phone' => '+8801712345678',
            'shipping_address' => '123 Test Street',
            'shipping_city' => 'Dhaka',
            'shipping_zip' => '1205',
            'payment_method' => 'cash_on_delivery',
        ], [
            'X-Cart-Session' => $this->sessionId,
        ]);

        $variant->refresh();
        $this->assertEquals($originalStock - 2, $variant->stock);
    }

    public function test_order_clears_cart_after_checkout(): void
    {
        $this->addItemToCart();

        $this->postJson('/api/v1/checkout', [
            'shipping_name' => 'Test User',
            'shipping_email' => 'test@example.com',
            'shipping_phone' => '+8801712345678',
            'shipping_address' => '123 Test Street',
            'shipping_city' => 'Dhaka',
            'shipping_zip' => '1205',
            'payment_method' => 'cash_on_delivery',
        ], [
            'X-Cart-Session' => $this->sessionId,
        ]);

        $cart = Cart::where('session_id', $this->sessionId)->first();
        $this->assertEquals(0, $cart->items->count());
    }

    public function test_cannot_checkout_with_empty_cart(): void
    {
        $response = $this->postJson('/api/v1/checkout', [
            'shipping_name' => 'Test User',
            'shipping_email' => 'test@example.com',
            'shipping_phone' => '+8801712345678',
            'shipping_address' => '123 Test Street',
            'shipping_city' => 'Dhaka',
            'shipping_zip' => '1205',
            'payment_method' => 'cash_on_delivery',
        ], [
            'X-Cart-Session' => $this->sessionId,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Cart is empty',
            ]);
    }

    public function test_guest_can_view_order_with_email(): void
    {
        $this->addItemToCart();

        $checkoutResponse = $this->postJson('/api/v1/checkout', [
            'shipping_name' => 'Test User',
            'shipping_email' => 'test@example.com',
            'shipping_phone' => '+8801712345678',
            'shipping_address' => '123 Test Street',
            'shipping_city' => 'Dhaka',
            'shipping_zip' => '1205',
            'payment_method' => 'cash_on_delivery',
        ], [
            'X-Cart-Session' => $this->sessionId,
        ]);

        $orderNumber = $checkoutResponse->json('order.order_number');

        $response = $this->getJson("/api/v1/orders/{$orderNumber}?email=test@example.com");

        $response->assertStatus(200)
            ->assertJson([
                'order_number' => $orderNumber,
            ]);
    }

    public function test_authenticated_user_can_view_their_orders(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()
            ->has(ProductVariant::factory()->state([
                'price' => 1000,
                'stock' => 10,
            ]), 'variants')
            ->create(['is_active' => true]);

        Order::factory()->create([
            'user_id' => $user->id,
            'order_number' => 'ORD-TEST123',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'order_number',
                        'total',
                        'status',
                    ],
                ],
            ]);
    }

    public function test_user_can_cancel_pending_order(): void
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/orders/{$order->order_number}/cancel");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Order cancelled successfully',
            ]);

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
    }
}
