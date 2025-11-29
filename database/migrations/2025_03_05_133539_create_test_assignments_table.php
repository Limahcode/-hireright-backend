<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
// use function Symfony\Component\Clock\now;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('test_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained('tests');
            $table->foreignId('candidate_id')->constrained('users');
            $table->foreignId('stage_test_id')->constrained('stage_test_mappings');
            $table->string('status')->default('assigned'); // assigned, in_progress, completed, expired, graded
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
$table->timestamp('expires_at')->nullable();
            $table->decimal('score', 8, 2)->nullable();
            $table->boolean('passed')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_assignments');
    }
};
