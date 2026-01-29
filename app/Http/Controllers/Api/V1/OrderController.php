<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CheckoutRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @group Orders
 *
 * APIs for managing orders and checkout
 */
class OrderController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private OrderService $orderService
    ) {}

    private function getSessionId(Request $request): string
    {
        return $request->header('X-Cart-Session') ?? \Illuminate\Support\Str::uuid()->toString();
    }

    public function index(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $orders = Order::where('user_id', Auth::id())
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($orders);
    }

    /**
     * Checkout
     *
     * Create order from cart. Clears cart after successful order creation.
     *
     * @header X-Cart-Session string Session ID for guest cart
     * @bodyParam shipping_name string required Full name. Example: John Doe
     * @bodyParam shipping_email string required Email. Example: john@example.com
     * @bodyParam shipping_phone string required Phone. Example: +8801712345678
     * @bodyParam shipping_address string required Address. Example: 123 Main St
     * @bodyParam shipping_city string required City. Example: Dhaka
     * @bodyParam shipping_zip string required Zip code. Example: 1205
     * @bodyParam payment_method string required Payment method. Example: stripe
     * @bodyParam customer_note string Customer note. Example: Please call before delivery
     *
     * @response 201 {
     *   "message": "Order created successfully",
     *   "order": {
     *     "order_number": "ORD-ABC123",
     *     "total": "40393.60",
     *     "status": "pending"
     *   }
     * }
     */
    public function store(CheckoutRequest $request)
    {
        try {
            // Get cart
            $sessionId = $this->getSessionId($request);
            $cart = $this->cartService->getOrCreateCart($sessionId);

            // Add guest email if not authenticated
            $data = $request->validated();
            if (!Auth::check()) {
                $data['guest_email'] = $request->shipping_email;
            }

            // Create order
            $order = $this->orderService->createOrderFromCart($cart, $data);

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function show(Request $request, string $orderNumber)
    {
        $query = Order::where('order_number', $orderNumber)
            ->with(['items.product', 'items.variant']);

        // Guests can view by email, users by their ID
        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            // Allow guest to view with email verification
            $email = $request->query('email');
            if (!$email) {
                return response()->json(['error' => 'Email required for guest orders'], 400);
            }
            $query->where('guest_email', $email);
        }

        $order = $query->firstOrFail();

        return response()->json($order);
    }

    public function cancel(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        try {
            $order = $this->orderService->cancelOrder($order);

            return response()->json([
                'message' => 'Order cancelled successfully',
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
