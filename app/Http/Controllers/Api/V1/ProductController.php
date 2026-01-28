<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\Api\V1\UploadProductImagesRequest;
use App\Services\ImageService;
use App\Models\ProductImage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = Product::query()
            ->with(['primaryImage', 'defaultVariant'])
            ->active()
            ->published()
            ->orderBy('sort_position')
            ->paginate($request->get('per_page', 15));

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uid)
    {
        $product = Product::where('uid', $uid)
            ->with(['images', 'variants', 'categories'])
            ->active()
            ->published()
            ->firstOrFail();

        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(product $product)
    {
        //
    }

    public function uploadImages(UploadProductImagesRequest $request, string $uid, ImageService $imageService)
    {
        $product = Product::where('uid', $uid)->firstOrFail();
        $uploadedImages = [];
        $isPrimaryIndex = $request->input('is_primary', 0);

        foreach ($request->file('images') as $index => $file) {
            // Upload and process image
            $imageData = $imageService->uploadProductImage($file);

            // Create product image record
            $productImage = ProductImage::create([
                'product_id' => $product->id,
                'url' => $imageData['url'],
                'path' => $imageData['path'],
                'name' => $imageData['name'],
                'mime_type' => $imageData['mime_type'],
                'size' => $imageData['size'],
                'is_primary' => $index === $isPrimaryIndex,
                'sort_order' => $index,
            ]);

            $uploadedImages[] = $productImage;
        }

        // If primary is set, remove primary from other images
        if ($request->has('is_primary')) {
            ProductImage::where('product_id', $product->id)
                ->where('id', '!=', $uploadedImages[$isPrimaryIndex]->id)
                ->update(['is_primary' => false]);
        }

        return response()->json([
            'message' => 'Images uploaded successfully',
            'images' => $uploadedImages,
        ], 201);
    }

    public function deleteImage(string $uid, ProductImage $image)
    {
        $product = Product::where('uid', $uid)->firstOrFail();

        if ($image->product_id !== $product->id) {
            return response()->json(['error' => 'Image not found'], 404);
        }

        // Delete file from storage
        app(ImageService::class)->deleteProductImage($image->path);

        // Delete database record
        $image->delete();

        return response()->json(['message' => 'Image deleted successfully']);
    }
}
