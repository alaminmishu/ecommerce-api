<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AddToCartRequest;
use App\Http\Requests\Api\V1\UpdateCartItemRequest;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * @group Shopping Cart
 *
 * APIs for managing shopping cart
 */
class CartController extends Controller
{
    public function __construct(
        private CartService $cartService
    ) {}

    private function getSessionId(Request $request): string
    {
        // Get from header or generate new
        return $request->header('X-Cart-Session') ?? Str::uuid()->toString();
    }

    /**
     * Get cart
     *
     * Get current cart with items. Use X-Cart-Session header for guest carts.
     *
     * @header X-Cart-Session string Session ID for guest cart. Example: abc-123-def
     *
     * @response 200 {
     *   "session_id": "abc-123",
     *   "cart": {"id": 1, "items": []},
     *   "total": 0,
     *   "item_count": 0
     * }
     */
    public function index(Request $request)
    {
        $sessionId = $this->getSessionId($request);
        $cart = $this->cartService->getOrCreateCart($sessionId);

        $cart->load(['items.product', 'items.variant']);

        return response()->json([
            'session_id' => $sessionId,
            'cart' => $cart,
            'total' => $cart->total,
            'item_count' => $cart->item_count,
        ]);
    }

    /**
     * Add item to cart
     *
     * Add a product variant to the shopping cart.
     *
     * @header X-Cart-Session string Session ID for guest cart
     * @bodyParam product_variant_id integer required Product variant ID. Example: 1
     * @bodyParam quantity integer required Quantity. Example: 2
     *
     * @response 201 {
     *   "message": "Item added to cart",
     *   "session_id": "abc-123",
     *   "item": {}
     * }
     */
    public function addItem(AddToCartRequest $request)
    {
        $sessionId = $this->getSessionId($request);
        $cart = $this->cartService->getOrCreateCart($sessionId);

        try {
            $item = $this->cartService->addItem(
                $cart,
                $request->product_variant_id,
                $request->quantity
            );

            return response()->json([
                'message' => 'Item added to cart',
                'session_id' => $sessionId,
                'item' => $item,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function updateItem(UpdateCartItemRequest $request, int $itemId)
    {
        $sessionId = $this->getSessionId($request);
        $cart = $this->cartService->getOrCreateCart($sessionId);

        try {
            $item = $this->cartService->updateQuantity(
                $cart,
                $itemId,
                $request->quantity
            );

            return response()->json([
                'message' => 'Cart updated',
                'item' => $item,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function removeItem(Request $request, int $itemId)
    {
        $sessionId = $this->getSessionId($request);
        $cart = $this->cartService->getOrCreateCart($sessionId);

        $this->cartService->removeItem($cart, $itemId);

        return response()->json([
            'message' => 'Item removed from cart'
        ]);
    }

    public function clear(Request $request)
    {
        $sessionId = $this->getSessionId($request);
        $cart = $this->cartService->getOrCreateCart($sessionId);

        $this->cartService->clearCart($cart);

        return response()->json([
            'message' => 'Cart cleared'
        ]);
    }
}
