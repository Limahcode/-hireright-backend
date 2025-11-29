<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('candidate_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_assignment_id')->constrained('test_assignments');
            $table->foreignId('question_id')->constrained('test_questions');
            $table->string('response_type'); // 'option', 'text', 'code', 'file'
            $table->foreignId('option_id')->nullable()->constrained('question_options')->onDelete('set null');
            $table->text('text_response')->nullable();
            $table->text('code_response')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('points_earned', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_responses');
    }
};
