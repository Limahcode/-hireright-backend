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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->enum('signup_strategy', ['form', 'google', 'facebook'])->default('form');
            $table->enum('reg_channel', ['web', 'mobile'])->nullable();
            $table->string('referral_code')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->string('firebase_device_token')->nullable();
            $table->date('dob')->nullable();
            $table->string('email_otp')->nullable();
            $table->timestamp('email_otp_expiry')->nullable();
            $table->string('phone_otp')->nullable();
            $table->timestamp('phone_otp_expiry')->nullable();
            $table->string('password_otp')->nullable();
            $table->timestamp('password_otp_expiry')->nullable();
            $table->string('login_otp')->nullable();
            $table->timestamp('login_otp_expiry')->nullable();
            $table->string('password');
            $table->boolean('email_verified')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('phone_verified')->default(false);
            $table->string('app_role'); // employer, candidate, admin
            $table->timestamp('last_seen')->nullable();
            $table->integer('login_count')->default(1);
            $table->integer('loyalty_points')->default(0);
            $table->timestamps();
            //
            $table->string('linkedin_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable(); 
            $table->string('youtube_url')->nullable(); 
            $table->string('tiktok_url')->nullable();  
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
