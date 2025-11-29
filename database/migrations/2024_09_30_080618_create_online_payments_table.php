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
        Schema::create('online_payments', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('reference', 190)->unique();
            $table->string('gateway_ref')->nullable(); // Gateway reference
            $table->string('gateway_code'); //
            $table->string('customer_email')->nullable(); // Customer email
            $table->boolean('verified')->default(false); // Verified status
            $table->boolean('viewed')->default(false); // Viewed status
            $table->boolean('settled')->default(false); // Settled status
            $table->boolean('pass_charges')->default(true); // Whether charges are passed to the customer
            $table->timestamp('last_verified')->nullable(); // Last verification timestamp
            $table->timestamp('initiated')->nullable(); // Payment initiation timestamp
            $table->timestamp('completed')->nullable(); // Payment completion timestamp
            $table->double('amount', 8, 2)->default(0); // Payment amount
            $table->double('gateway_fee', 8, 2)->default(0); // Gateway fee
            $table->string('currency_code')->nullable(); // Currency code
            $table->string('status')->nullable(); // Payment status

            $table->unsignedBigInteger('order_id'); // Link to the order
            $table->unsignedBigInteger('customer_id'); // Link to the customer/user
            $table->unsignedBigInteger('store_id'); // Link to the store
            // 
            $table->timestamps(); // Created at and updated at

            // Foreign keys
            $table->foreign('customer_id')->references('id')->on('users');
            $table->foreign('order_id')->references('id')->on('orders');
            $table->foreign('store_id')->references('id')->on('stores');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_payments');
    }
};
