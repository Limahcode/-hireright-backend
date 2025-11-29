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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('applied'); // Changed from enum to string
            $table->text('cover_letter')->nullable();
            $table->json('answers')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Changed job_listing_id to job_id
            $table->foreignId('job_id')->constrained('job_listings')->cascadeOnDelete();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            // Added company_id
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // Updated unique constraint to use job_id instead of job_listing_id
            $table->unique(['job_id', 'user_id']);

            // Updated indexes to use job_id instead of job_listing_id
            $table->index(['user_id', 'status']);
            $table->index(['job_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};