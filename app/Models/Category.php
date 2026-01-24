<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uid',
        'en_name',
        'bn_name',
        'slug',
        'en_description',
        'bn_description',
        'parent_id',
        'image_url',
        'has_child',
        'is_active',
        'sort_position',
        'level',
        'path',
    ];

    protected $casts = [
        'has_child' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Auto-generate UID and slug
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->uid)) {
                $category->uid = 'C-' . strtoupper(Str::random(6));
            }

            if (empty($category->slug)) {
                $category->slug = Str::slug($category->en_name);
            }

            // Set level and path
            if ($category->parent_id) {
                $parent = self::find($category->parent_id);
                $category->level = $parent->level + 1;
                $category->path = $parent->path . '/' . $parent->id;
            } else {
                $category->level = 0;
                $category->path = '';
            }
        });
    }

    // Relationships
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_position');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_categories')
            ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }
}
