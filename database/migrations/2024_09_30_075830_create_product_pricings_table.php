<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_pricings', function (Blueprint $table) {
            $table->id(); // Big integer for primary key
            $table->double('current_price')->default(0.0);
            $table->double('sales_price')->default(0.0);
            $table->boolean('on_sales')->default(false);

            $table->string('region_code')->nullable();
            $table->string('country_code')->default('NGN');

            $table->unsignedBigInteger('product_id')->nullable(); // FK to product

            $table->timestamps();

            // Foreign key constraint
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            // Unique index on country_code, region_code, and product_id
            $table->unique(['country_code', 'region_code', 'product_id'], 'unique_country_region_product');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_pricings');
    }
};
