<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->nullable()->index();
            $table->string('barcode')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->integer('low_stock_threshold')->default(5);
            $table->decimal('weight', 8, 2)->nullable(); // kg
            $table->json('attributes')->nullable(); // {color: "red", size: "M"}
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_position')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'sku', 'is_default', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
