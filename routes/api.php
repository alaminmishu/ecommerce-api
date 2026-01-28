<?php

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

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('products', [ProductController::class, 'store']);
        Route::put('products/{product}', [ProductController::class, 'update']);
        Route::delete('products/{product}', [ProductController::class, 'destroy']);
        // Image upload routes
        Route::post('products/{product}/images', [ProductController::class, 'uploadImages']);
        Route::delete('products/{product}/images/{image}', [ProductController::class, 'deleteImage']);
    });
});
