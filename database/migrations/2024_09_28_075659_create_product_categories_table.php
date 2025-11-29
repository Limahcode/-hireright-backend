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
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id(); // Big integer for primary key
            $table->string('name'); // Category name
            $table->text('description')->nullable(); // Optional description
            //
            $table->boolean('is_featured')->default(false);
            //
            $table->string('slug', 190); // SEO-friendly slug
            $table->string('tags', 1000)->nullable(); // Optional tags
            //
            $table->string('base_url')->nullable(); // Optional base URL for the category
            $table->string('image_key')->nullable(); // Optional image key for category image
            //
            $table->unsignedBigInteger('parent_id')->nullable(); // For sub-categories
            $table->unsignedBigInteger('store_id')->nullable(); // For store-specific categories, null for global categories
            //
            $table->timestamps();
            // Foreign key constraints
            $table->foreign('parent_id')->references('id')->on('product_categories')->onDelete('cascade'); // Cascading delete for sub-categories
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('set null'); // Set null if store is deleted
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};
