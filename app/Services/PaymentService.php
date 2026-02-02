<?php

namespace App\Services;

use App\Models\Order;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPaymentIntent(Order $order): array
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $order->total * 100, // Convert to cents
                'currency' => 'bdt',
                'metadata' => [
                    'order_number' => $order->order_number,
                    'order_id' => $order->id,
                ],
                'description' => "Order #{$order->order_number}",
            ]);

            // Update order with payment intent ID
            $order->update([
                'payment_intent_id' => $paymentIntent->id,
                'payment_details' => json_encode([
                    'payment_intent_id' => $paymentIntent->id,
                    'client_secret' => $paymentIntent->client_secret,
                ]),
            ]);

            return [
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Payment creation failed: '.$e->getMessage());
        }
    }

    public function confirmPayment(Order $order, string $paymentIntentId): Order
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            if ($paymentIntent->status === 'succeeded') {
                $order->update([
                    'payment_status' => 'paid',
                    'payment_intent_id' => $paymentIntentId,
                    'paid_at' => now(),
                    'status' => 'processing',
                ]);
            } elseif ($paymentIntent->status === 'requires_payment_method') {
                $order->update([
                    'payment_status' => 'failed',
                ]);
            }

            return $order;
        } catch (\Exception $e) {
            throw new \Exception('Payment confirmation failed: '.$e->getMessage());
        }
    }

    public function refundPayment(Order $order): bool
    {
        if (! $order->payment_intent_id) {
            throw new \Exception('No payment to refund');
        }

        try {
            $refund = \Stripe\Refund::create([
                'payment_intent' => $order->payment_intent_id,
            ]);

            $order->update([
                'payment_status' => 'refunded',
            ]);

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Refund failed: '.$e->getMessage());
        }
    }
}
