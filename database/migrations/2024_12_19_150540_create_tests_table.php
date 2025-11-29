<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->string('creator_type'); // Polymorphic relation
            $table->unsignedBigInteger('creator_id'); // Polymorphic relation
            $table->integer('time_limit')->default(0); // 0 means no limit
            $table->decimal('passing_score', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->enum('submission_type', ['online', 'document_upload', 'both'])->default('online');
            $table->enum('visibility_type', ['view_before_start', 'hidden_until_start'])->default('view_before_start');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};
