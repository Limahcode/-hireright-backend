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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Reference to the user receiving the notification
            $table->string('title'); // Notification title
            $table->text('message'); // Notification message content
            $table->string('type')->default('general'); // Type of notification (e.g., 'order', 'promotion', 'reminder')
            $table->boolean('is_read')->default(false); // Whether the user has read the notification
            $table->string('status')->default('active'); // 'active', 'archived', deleted
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
