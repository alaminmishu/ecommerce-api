<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrderFromCart(Cart $cart, array $data): Order
    {
        // Validate cart has items
        if ($cart->items->isEmpty()) {
            throw new \Exception('Cart is empty');
        }

        // Validate stock
        foreach ($cart->items as $item) {
            if ($item->variant->stock < $item->quantity) {
                throw new \Exception("Insufficient stock for {$item->product->en_name}");
            }
        }

        return DB::transaction(function () use ($cart, $data) {
            // Calculate totals
            $subtotal = $cart->total;
            $tax = $data['tax'] ?? 0;
            $shipping = $data['shipping'] ?? 0;
            $discount = $data['discount'] ?? 0;
            $total = $subtotal + $tax + $shipping - $discount;

            // Create order
            $order = Order::create([
                'user_id' => $cart->user_id,
                'guest_email' => $data['guest_email'] ?? null,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping' => $shipping,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $data['payment_method'] ?? 'cash_on_delivery',
                'shipping_name' => $data['shipping_name'],
                'shipping_email' => $data['shipping_email'],
                'shipping_phone' => $data['shipping_phone'],
                'shipping_address' => $data['shipping_address'],
                'shipping_city' => $data['shipping_city'],
                'shipping_state' => $data['shipping_state'] ?? null,
                'shipping_zip' => $data['shipping_zip'],
                'shipping_country' => $data['shipping_country'] ?? 'BD',
                'customer_note' => $data['customer_note'] ?? null,
            ]);

            // Create order items
            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_variant_id' => $cartItem->product_variant_id,
                    'product_name' => $cartItem->product->en_name,
                    'product_sku' => $cartItem->variant->sku,
                    'variant_attributes' => $cartItem->variant->attributes,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'subtotal' => $cartItem->price * $cartItem->quantity,
                ]);

                // Reduce stock
                $cartItem->variant->decrement('stock', $cartItem->quantity);
            }

            // Clear cart
            $cart->items()->delete();

            return $order->load('items');
        });
    }

    public function updateOrderStatus(Order $order, string $status): Order
    {
        $validStatuses = ['pending', 'processing', 'completed', 'cancelled', 'failed'];

        if (!in_array($status, $validStatuses)) {
            throw new \Exception('Invalid status');
        }

        $order->update(['status' => $status]);

        if ($status === 'completed') {
            $order->update(['completed_at' => now()]);
        }

        return $order;
    }

    public function updatePaymentStatus(Order $order, string $paymentStatus, ?string $paymentIntentId = null): Order
    {
        $validStatuses = ['pending', 'paid', 'failed', 'refunded'];

        if (!in_array($paymentStatus, $validStatuses)) {
            throw new \Exception('Invalid payment status');
        }

        $updateData = ['payment_status' => $paymentStatus];

        if ($paymentIntentId) {
            $updateData['payment_intent_id'] = $paymentIntentId;
        }

        if ($paymentStatus === 'paid') {
            $updateData['paid_at'] = now();
        }

        $order->update($updateData);

        return $order;
    }

    public function cancelOrder(Order $order): Order
    {
        if (!$order->canBeCancelled()) {
            throw new \Exception('Order cannot be cancelled');
        }

        DB::transaction(function () use ($order) {
            // Restore stock
            foreach ($order->items as $item) {
                $item->variant->increment('stock', $item->quantity);
            }

            // Update status
            $order->update(['status' => 'cancelled']);
        });

        return $order;
    }
}
