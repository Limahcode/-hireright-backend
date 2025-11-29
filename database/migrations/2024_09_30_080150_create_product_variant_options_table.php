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
        Schema::create('product_variant_options', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('name'); // Option name
            $table->boolean('active')->default(true); // Status of the variant option
            $table->unsignedBigInteger('variant_id'); // Foreign key to product_variants
            $table->unsignedBigInteger('product_id'); // Foreign key to products

            $table->timestamps(); // created_at and updated_at

            // Foreign key constraints
            $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variant_options');
    }
};
