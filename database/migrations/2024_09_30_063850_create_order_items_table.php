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
            $table->id(); // Primary Key
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id')->nullable(); // If product is deleted, order items remain
            $table->string('product_name');
            $table->string('product_barcode')->nullable();
            $table->string('combination_name')->nullable();
            $table->unsignedBigInteger('product_category_id')->nullable();

            $table->double('price');
            $table->integer('qty');
            $table->double('vat')->nullable();
            $table->boolean('vat_exempted')->default(false);
            $table->boolean('on_sales')->default(false);
            // Foreign key references
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            
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
