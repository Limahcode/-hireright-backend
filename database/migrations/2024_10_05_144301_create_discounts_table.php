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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('name'); // Discount name
            $table->string('code')->nullable(); // Discount code
            $table->text('description')->nullable(); // Description
            $table->double('min_purchase_vol')->nullable(); // Minimum purchase volume
            $table->integer('min_purchase_qty')->nullable(); // Minimum purchase quantity
            $table->integer('usage_limit')->nullable(); // Usage limit
            $table->integer('usage_count')->default(0); // Usage count
            $table->integer('usage_per_user')->nullable(); // Usage per user
            $table->enum('platform', ['web', 'mobile'])->nullable();
            $table->enum('type', ['PERCENTAGE', 'FIXED'])->nullable(); // Type (percentage or fixed)
            $table->enum('status', ['ACTIVE', 'DISABLED', 'EXPIRED'])->default('ACTIVE'); // Status
            $table->enum('application', ['ALL_PRODUCTS', 'SELECT_PRODUCTS', 'SELECT_CATEGORY'])->default('ALL_PRODUCTS'); // Application
            $table->double('value')->default(0.0); // Discount value
            $table->date('start_date'); // Start date
            $table->date('end_date')->nullable(); // End date
            $table->enum('target', ['QTY', 'VOL', 'NONE'])->default('NONE'); // Target
            $table->enum('eligibility', ['ALL_CUSTOMERS', 'SELECT_GROUP', 'SELECT_CUSTOMERS'])->default('ALL_CUSTOMERS'); // Eligibility

            // Foreign keys
            $table->foreignId('store_id')->constrained()->onDelete('cascade'); // Foreign key to stores
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->onDelete('cascade'); // Foreign key to product categories
            
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
