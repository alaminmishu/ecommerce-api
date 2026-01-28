<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;

class ImageService
{
    public function uploadProductImage(UploadedFile $file, string $directory = 'products'): array
    {
        // Generate unique filename
        $filename = Str::uuid() . '.webp';
        $path = $directory . '/' . $filename;

        // Process image (resize & optimize)
        $image = Image::read($file);

        // Resize to max 1200px width maintaining aspect ratio
        if ($image->width() > 1200) {
            $image->scale(width: 1200);
        }

        // Convert to WebP and save
        $encoded = $image->toWebp(quality: 85);
        Storage::disk('public')->put($path, $encoded);

        return [
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'name' => $filename,
            'mime_type' => 'image/webp',
            'size' => Storage::disk('public')->size($path),
        ];
    }

    public function deleteProductImage(string $path): bool
    {
        return Storage::disk('public')->delete($path);
    }
}
