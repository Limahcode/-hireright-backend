<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('test_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained('tests')->onDelete('cascade');
            $table->text('question_text');
            $table->string('question_type'); // // multiple_choice, single_choice, text, code, file_upload, information_only
            $table->decimal('points', 8, 2)->default(0);
            $table->integer('order')->default(0);
            $table->json('settings')->nullable(); // Stores question settings
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_questions');
    }
};
