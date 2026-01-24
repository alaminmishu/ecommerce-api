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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('uid', 20)->unique(); // P-XXXXXX
            $table->string('sku')->nullable()->index(); // External/supplier SKU
            $table->string('type')->default('simple'); // simple, configurable, bundle
            $table->string('en_name');
            $table->string('bn_name')->nullable();
            $table->string('slug')->unique();
            $table->text('en_description')->nullable();
            $table->text('bn_description')->nullable();
            $table->text('en_short_description')->nullable();
            $table->text('bn_short_description')->nullable();
            $table->string('video_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(false);
            $table->integer('sort_position')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['uid', 'slug', 'type', 'is_active']);
            $table->index(['is_featured', 'is_new', 'published_at']);
            $table->index('sort_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
