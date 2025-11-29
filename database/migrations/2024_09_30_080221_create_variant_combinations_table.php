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
        Schema::create('variant_combinations', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('name'); // Combination name
            $table->string('code')->nullable(); // Combination code
            $table->double('current_price')->default(0.0); // Current price for the variant combination
            $table->double('sales_price')->default(0.0); // Sales price
            $table->boolean('on_sales')->default(false); 
            $table->string('sku')->nullable(); 
            $table->string('barcode')->nullable(); // Barcode for the product
            $table->integer('quantity')->default(0); // Quantity in stock
            $table->boolean('track_quantity')->default(false);
            $table->integer('reorder_point')->nullable(); // Reorder point for restocking
            $table->boolean('active')->default(true); 

            $table->unsignedBigInteger('product_id'); // Foreign key to the product

            $table->timestamps(); // created_at and updated_at
            // Foreign key constraints
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variant_combinations');
    }
};
