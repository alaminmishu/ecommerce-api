<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uid' => $this->uid,
            'sku' => $this->sku,
            'type' => $this->type,
            'name' => $this->en_name,
            'nameBn' => $this->bn_name,
            'slug' => $this->slug,
            'description' => $this->en_description,
            'shortDescription' => $this->en_short_description,
            'videoUrl' => $this->video_url,
            'isActive' => $this->is_active,
            'isFeatured' => $this->is_featured,
            'isNew' => $this->is_new,
            'publishedAt' => $this->published_at,

            // Relationships
            'price' => $this->whenLoaded('defaultVariant', function () {
                return $this->defaultVariant ? [
                    'regular' => (float) $this->defaultVariant->price,
                    'compare' => $this->defaultVariant->compare_price ? (float) $this->defaultVariant->compare_price : null,
                    'discount' => $this->defaultVariant->compare_price
                        ? round((($this->defaultVariant->compare_price - $this->defaultVariant->price) / $this->defaultVariant->compare_price) * 100)
                        : null,
                ] : null;
            }),

            'stock' => $this->whenLoaded('defaultVariant', fn () => $this->defaultVariant?->stock),
            'inStock' => $this->whenLoaded('defaultVariant', fn () => $this->defaultVariant?->stock > 0),

            'image' => $this->whenLoaded('primaryImage', fn () => $this->primaryImage?->url),

            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
