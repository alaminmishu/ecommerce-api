<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create categories first
        $electronics = Category::create([
            'uid' => 'C-ELEC01',
            'en_name' => 'Electronics',
            'bn_name' => 'ইলেকট্রনিক্স',
            'slug' => 'electronics',
            'is_active' => true,
            'sort_position' => 1,
        ]);

        $refrigerator = Category::create([
            'uid' => 'C-REFR01',
            'en_name' => 'Refrigerator',
            'bn_name' => 'রেফ্রিজারেটর',
            'slug' => 'refrigerator',
            'parent_id' => $electronics->id,
            'is_active' => true,
            'sort_position' => 1,
        ]);

        // Create 10 products with variants
        Product::factory(10)
            ->has(ProductVariant::factory()->count(1), 'variants')
            ->create()
            ->each(function ($product) use ($refrigerator) {
                // Attach  category
                $product->categories()->attach($refrigerator->id, [
                    'is_primary' => true,
                    'sort_position' => 0,
                ]);
            });
    }
}
