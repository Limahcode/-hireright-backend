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
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_image')->nullable()->after('website');
            $table->string('cover_image')->nullable()->after('profile_image');
            $table->string('resume')->nullable()->after('cover_image');
            $table->string('cover_letter_file')->nullable()->after('resume');
            $table->string('portfolio')->nullable()->after('cover_letter_file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'profile_image',
                'cover_image',
                'resume',
                'cover_letter_file',
                'portfolio'
            ]);
        });
    }
};