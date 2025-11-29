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
        Schema::create('job_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->json('keywords')->nullable();
            $table->json('locations')->nullable();
            $table->json('job_types')->nullable();
            $table->json('experience_levels')->nullable();
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->json('skills')->nullable();
            $table->json('companies')->nullable();
            $table->string('frequency')->default('daily'); // ['daily', 'weekly', 'instant']
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sent_at')->nullable();

            $table->foreignId('user_id')->constrained();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_alerts');
    }
};
