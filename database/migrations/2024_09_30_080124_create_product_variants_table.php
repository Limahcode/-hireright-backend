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
            $table->id(); // Primary key
            $table->string('name'); // Variant name
            $table->string('code')->nullable(); // Optional code for variant
            $table->string('slug')->nullable(); // Slug for SEO
            $table->boolean('active')->default(true); // Status of variant
            $table->unsignedBigInteger('product_id'); // Foreign key to the product

            $table->timestamps(); // created_at and updated_at

            // Foreign key constraint to the products table
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
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
