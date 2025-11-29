<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();

            // Defining foreign keys explicitly
            $table->unsignedBigInteger('category_id')->nullable(); // Category relationship
            $table->unsignedBigInteger('author_id'); // Author relationship (User table)

            // Defining relationships as foreign keys
            $table->foreign('category_id')->references('id')->on('post_categories');
            $table->foreign('author_id')->references('id')->on('users');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
};
