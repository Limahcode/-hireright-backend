<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('response_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_response_id')->constrained('candidate_responses');
            $table->string('file_path');
            $table->string('disk'); // e.g., 's3', 'local',
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('response_attachments');
    }
};
