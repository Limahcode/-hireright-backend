<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoresTable extends Migration
{
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id(); // Big integer by default for PK
            $table->unsignedBigInteger('owner_id'); // Foreign key to the user (vendor)
            $table->string('store_name');
            $table->string('slug')->unique();
            $table->string('code')->unique();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('url')->nullable();
            $table->string('currency_code')->nullable();
            $table->boolean('apply_vat')->default(false);
            $table->double('vat_percent')->default(0.0);
            $table->boolean('apply_service_charge')->default(false);
            $table->enum('service_charge_type', ['fixed', 'percent'])->default('fixed');
            $table->double('service_charge')->default(0.0);

            $table->enum('status', ['active', 'inactive'])->default('active'); // Store status
            //
            $table->string('region_code')->nullable();
            $table->string('country_code')->default('NGN');
            // Opening and closing times
            $table->time('opening_time')->nullable(); // Store opening time
            $table->time('closing_time')->nullable(); // Store closing time
            //
            $table->timestamps();
            $table->softDeletes(); // Soft deletes column
            // Foreign key constraints
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stores');
    }
}
