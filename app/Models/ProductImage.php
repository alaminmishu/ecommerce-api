<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'url',
        'path',
        'name',
        'mime_type',
        'size',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    // Relationship
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Scope
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
