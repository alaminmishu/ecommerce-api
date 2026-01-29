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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('product_variant_id')->constrained();

            $table->string('product_name'); // Lock product name
            $table->string('product_sku')->nullable();
            $table->json('variant_attributes')->nullable(); // {color: "red", size: "M"}

            $table->integer('quantity');
            $table->decimal('price', 10, 2); // Lock price
            $table->decimal('subtotal', 10, 2); // quantity * price

            $table->timestamps();

            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
