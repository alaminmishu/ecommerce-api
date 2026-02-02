<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private function createOrder(): Order
    {
        $product = Product::factory()
            ->has(ProductVariant::factory()->state([
                'price' => 1000,
                'stock' => 10,
            ]), 'variants')
            ->create(['is_active' => true]);

        return Order::factory()->create([
            'guest_email' => 'payment@test.com',
            'total' => 1000,
            'payment_method' => 'stripe',
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);
    }

    public function test_can_create_payment_intent(): void
    {
        $order = $this->createOrder();

        $response = $this->postJson("/api/v1/orders/{$order->order_number}/payment-intent", [
            'email' => $order->guest_email,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'client_secret',
                'payment_intent_id',
                'amount',
            ]);
    }

    public function test_cannot_create_payment_intent_for_paid_order(): void
    {
        $order = $this->createOrder();
        $order->update(['payment_status' => 'paid']);

        $response = $this->postJson("/api/v1/orders/{$order->order_number}/payment-intent", [
            'email' => $order->guest_email,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Order already paid',
            ]);
    }

    public function test_cannot_create_payment_intent_for_cancelled_order(): void
    {
        $order = $this->createOrder();
        $order->update(['status' => 'cancelled']);

        $response = $this->postJson("/api/v1/orders/{$order->order_number}/payment-intent", [
            'email' => $order->guest_email,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Order is cancelled',
            ]);
    }

    public function test_payment_intent_updates_order(): void
    {
        $order = $this->createOrder();

        $response = $this->postJson("/api/v1/orders/{$order->order_number}/payment-intent", [
            'email' => $order->guest_email,
        ]);

        $response->assertStatus(200);

        $order->refresh();
        $this->assertNotNull($order->payment_intent_id);
        $this->assertNotNull($order->payment_details);
    }

    public function test_payment_amount_matches_order_total(): void
    {
        $order = $this->createOrder();
        $order->update(['total' => 5000]);

        $response = $this->postJson("/api/v1/orders/{$order->order_number}/payment-intent", [
            'email' => $order->guest_email,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'amount' => '5000.00',
            ]);
    }
}
