<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('test_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained('tests')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->integer('time_limit')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_downloadable')->default(false);
            $table->string('submission_type')->default('online'); // 'online', 'document_upload', 'both'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_sections');
    }
};
