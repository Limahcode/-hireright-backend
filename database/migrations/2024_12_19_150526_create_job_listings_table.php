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
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('requirements');
            $table->text('responsibilities');
            $table->text('benefits')->nullable();
            $table->string('employment_type'); // ['full_time', 'part_time', 'contract']
            $table->string('work_mode'); // ['remote', 'hybrid', 'onsite']
            $table->integer('positions_available')->default(1);
            $table->string('experience_level');
            $table->integer('min_years_experience')->default(0);
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->string('salary_currency', 3)->default('NGN');
            $table->boolean('hide_salary')->default(false);
            $table->string('location');
            $table->json('remote_regions')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->string('reference_code')->unique();
            $table->string('status')->default('published'); // ['draft', 'published', 'closed', 'archived']
            $table->timestamps();
            $table->softDeletes();
            //
            // $table->foreignId('test_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            // 
            $table->index(['status', 'deadline']);
            $table->index(['company_id', 'status']);
            $table->index(['is_published', 'deadline']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};
