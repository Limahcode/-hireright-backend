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
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('reference')->unique();
            $table->text('order_desc')->nullable();
            $table->string('discount_code')->nullable();
            $table->string('platform')->nullable(); // web or mobile
            $table->string('currency_code')->nullable();
            $table->string('payment_option')->nullable(); // ['cash', 'credit_card', etc.]
            $table->string('delivery_option')->nullable(); // ['pickup', 'delivery']
            $table->string('customer_email')->nullable(); // ['pickup', 'delivery']
            $table->double('total_qty')->default(0);
            $table->double('total')->default(0);
            $table->double('subtotal')->default(0);
            $table->double('total_discount')->default(0);
            $table->double('loyalty_discount')->default(0);
            $table->double('total_paid')->default(0);
            $table->double('balance')->default(0);
            $table->double('service_charge')->default(0);
            $table->double('gateway_fee')->default(0);
            $table->double('vat')->default(0);
            // This is used to track the status of orders as it moves 
            // from new to processing, tp in transit to delivered. 
            $table->string('status')->nullable();   
            // 'staged' is used to track all orders created with instant payment option
            // The flow is, when 'instant' payment orders are created, they get 'staged',
            // and a payment entry is created to accept payment. When/if the payment is 
            // successful, the order will be unstaged, and the status changed to new,
            // and the payment entry also marked as successful.
            // If the payment fails, the staged order remains staged and we can delete all staged
            // orders in the future.
            // If an order is created with pay on delivery (pod) payment option,
            // 'staged' will be set to false. 
            // And all staged orders are exempted from reports.
            $table->boolean('staged')->default(true); 
            $table->text('delivery_json')->nullable(); // Stored as JSON
            $table->unsignedBigInteger('customer_id'); // Foreign key
            $table->unsignedBigInteger('store_id'); // Foreign key

            // 
            $table->timestamps();

            // Foreign key reference
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade'); // Customer linked by user ID
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
