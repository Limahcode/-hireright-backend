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
        Schema::create('company_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('job_title')->nullable();
            $table->string('department')->nullable();
            $table->json('permissions')->nullable(); 
            $table->boolean('is_admin')->default(false); 
            $table->json('notification_preferences')->nullable();
            $table->string('status')->default('active'); // ['active', 'inactive']
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'user_id']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_staff');
    }
};
