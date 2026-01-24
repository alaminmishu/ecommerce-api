<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uid',
        'sku',
        'type',
        'en_name',
        'bn_name',
        'slug',
        'en_description',
        'bn_description',
        'en_short_description',
        'bn_short_description',
        'video_url',
        'is_active',
        'is_featured',
        'is_new',
        'sort_position',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'published_at' => 'datetime',
    ];

    // Auto-generate UID on creation
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->uid)) {
                $product->uid = 'P-' . strtoupper(Str::random(6));
            }

            if (empty($product->slug)) {
                $product->slug = Str::slug($product->en_name);
            }
        });
    }

    // Relationships will go here (next step)

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function defaultVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }

    // Query Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
