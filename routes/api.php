<?php

use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('v1')->group(function () {
    // Public routes
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{uid}', [ProductController::class, 'show']);

    // Cart routes (public - works for guests and auth users)
    Route::get('cart', [CartController::class, 'index']);
    Route::post('cart/items', [CartController::class, 'addItem']);
    Route::put('cart/items/{itemId}', [CartController::class, 'updateItem']);
    Route::delete('cart/items/{itemId}', [CartController::class, 'removeItem']);
    Route::delete('cart', [CartController::class, 'clear']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('products', [ProductController::class, 'store']);
        Route::put('products/{product}', [ProductController::class, 'update']);
        Route::delete('products/{product}', [ProductController::class, 'destroy']);
        // Image upload routes
        Route::post('products/{uid}/images', [ProductController::class, 'uploadImages']);
        Route::delete('products/{uid}/images/{image}', [ProductController::class, 'deleteImage']);
    });
});
