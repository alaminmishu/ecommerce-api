<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AddToCartRequest;
use App\Http\Requests\Api\V1\UpdateCartItemRequest;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService
    ) {}

    public function index(Request $request)
    {
        $sessionId = $request->header('X-Cart-Session') ?? $request->session()->getId();
        $cart = $this->cartService->getOrCreateCart($sessionId);

        $cart->load(['items.product', 'items.variant']);

        return response()->json([
            'cart' => $cart,
            'total' => $cart->total,
            'itemCount' => $cart->item_count,
        ]);
    }

    public function addItem(AddToCartRequest $request)
    {
        $sessionId = $request->header('X-Cart-Session') ?? $request->session()->getId();
        $cart = $this->cartService->getOrCreateCart($sessionId);

        try {
            $item = $this->cartService->addItem(
                $cart,
                $request->product_variant_id,
                $request->quantity
            );

            return response()->json([
                'message' => 'Item added to cart',
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
        $sessionId = $request->header('X-Cart-Session') ?? $request->session()->getId();
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
        $sessionId = $request->header('X-Cart-Session') ?? $request->session()->getId();
        $cart = $this->cartService->getOrCreateCart($sessionId);

        $this->cartService->removeItem($cart, $itemId);

        return response()->json([
            'message' => 'Item removed from cart'
        ]);
    }

    public function clear(Request $request)
    {
        $sessionId = $request->header('X-Cart-Session') ?? $request->session()->getId();
        $cart = $this->cartService->getOrCreateCart($sessionId);

        $this->cartService->clearCart($cart);

        return response()->json([
            'message' => 'Cart cleared'
        ]);
    }
}
