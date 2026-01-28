<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function getOrCreateCart(?string $sessionId = null): Cart
    {
        if (Auth::check()) {
            // Authenticated user
            $cart = Cart::firstOrCreate(
                ['user_id' => Auth::id()],
                ['expires_at' => now()->addDays(30)]
            );

            // Merge session cart if exists
            if ($sessionId) {
                $this->mergeSessionCart($cart, $sessionId);
            }
        } else {
            // Guest user
            $cart = Cart::firstOrCreate(
                ['session_id' => $sessionId],
                ['expires_at' => now()->addDays(7)]
            );
        }

        return $cart;
    }

    public function addItem(Cart $cart, int $variantId, int $quantity = 1): CartItem
    {
        $variant = ProductVariant::with('product')->findOrFail($variantId);

        // Check stock
        if ($variant->stock < $quantity) {
            throw new \Exception('Insufficient stock');
        }

        // Check if item already in cart
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_variant_id', $variantId)
            ->first();

        if ($cartItem) {
            // Update quantity
            $newQuantity = $cartItem->quantity + $quantity;

            if ($variant->stock < $newQuantity) {
                throw new \Exception('Insufficient stock');
            }

            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            // Create new cart item
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $variant->product_id,
                'product_variant_id' => $variantId,
                'quantity' => $quantity,
                'price' => $variant->price, // Lock current price
            ]);
        }

        return $cartItem->load(['product', 'variant']);
    }

    public function updateQuantity(Cart $cart, int $itemId, int $quantity): CartItem
    {
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->firstOrFail();

        if ($quantity <= 0) {
            throw new \Exception('Quantity must be greater than 0');
        }

        // Check stock
        if ($cartItem->variant->stock < $quantity) {
            throw new \Exception('Insufficient stock');
        }

        $cartItem->update(['quantity' => $quantity]);

        return $cartItem->load(['product', 'variant']);
    }

    public function removeItem(Cart $cart, int $itemId): bool
    {
        return CartItem::where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->delete();
    }

    public function clearCart(Cart $cart): bool
    {
        return $cart->items()->delete();
    }

    private function mergeSessionCart(Cart $userCart, string $sessionId): void
    {
        $sessionCart = Cart::where('session_id', $sessionId)->first();

        if ($sessionCart) {
            foreach ($sessionCart->items as $item) {
                $this->addItem($userCart, $item->product_variant_id, $item->quantity);
            }

            // Delete session cart
            $sessionCart->delete();
        }
    }
}
