<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function createPaymentIntent(Request $request, string $orderNumber)
    {
        $request->validate([
            'email' => 'required_without:auth|email',
        ]);

        // Find order
        $query = Order::where('order_number', $orderNumber);

        if (auth('sanctum')->check()) {
            $query->where('user_id', auth('sanctum')->id());
        } else {
            $email = $request->input('email');
            $query->where('guest_email', $email);
        }

        $order = $query->firstOrFail();

        // Validate order can be paid
        if ($order->payment_status === 'paid') {
            return response()->json(['error' => 'Order already paid'], 400);
        }

        if ($order->status === 'cancelled') {
            return response()->json(['error' => 'Order is cancelled'], 400);
        }

        try {
            $paymentData = $this->paymentService->createPaymentIntent($order);

            return response()->json([
                'client_secret' => $paymentData['client_secret'],
                'payment_intent_id' => $paymentData['payment_intent_id'],
                'amount' => $order->total,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function confirmPayment(Request $request, string $orderNumber)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        // Find order
        $query = Order::where('order_number', $orderNumber);

        if (auth('sanctum')->check()) {
            $query->where('user_id', auth('sanctum')->id());
        } else {
            $email = $request->input('email');
            if (!$email) {
                return response()->json(['error' => 'Email required'], 400);
            }
            $query->where('guest_email', $email);
        }

        $order = $query->firstOrFail();

        try {
            $order = $this->paymentService->confirmPayment(
                $order,
                $request->payment_intent_id
            );

            return response()->json([
                'message' => 'Payment confirmed',
                'order' => $order->load('items'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
