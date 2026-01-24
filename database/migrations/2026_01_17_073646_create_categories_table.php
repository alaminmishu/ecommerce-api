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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('uid', 20)->unique();
            $table->string('en_name');
            $table->string('bn_name')->nullable();
            $table->string('slug')->unique();
            $table->text('en_description')->nullable();
            $table->text('bn_description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->cascadeOnDelete();
            $table->string('image_url')->nullable();
            $table->boolean('has_child')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_position')->default(0);
            $table->integer('level')->default(0); // Tree depth
            $table->string('path')->nullable(); // /1/3/5 for queries
            $table->softDeletes();
            $table->timestamps();

            $table->index(['uid', 'slug', 'parent_id', 'is_active']);
            $table->index(['level', 'path']);
            $table->index('sort_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
