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
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('gateway_name', 100);
            $table->string('gateway_code', 100)->unique();
            $table->string('currency_code', 100);
            $table->enum('status', ['active', 'disabled'])->default('active');
            $table->boolean('is_default')->default(false);

            // Common fields across gateways
            $table->string('live_secret_key')->nullable();
            $table->string('live_public_key', 190)->nullable();
            $table->string('test_secret_key')->nullable();
            $table->string('test_public_key', 190)->nullable();

            // Fields for Paystack, Flutterwave, Fincra, Interswitch
            $table->boolean('live_validated')->default(false);
            $table->boolean('test_validated')->default(false);
            $table->double('capped_at')->nullable();
            $table->double('percent')->nullable();
            $table->double('surcharge')->nullable();

            // Flutterwave specific
            $table->string('live_encryption_key')->nullable();
            $table->string('test_encryption_key')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('gateway_name', 'gateway_code_idx');
            $table->unique(['gateway_code', 'currency_code'], 'gateway_currency_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
