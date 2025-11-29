<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // Big integer by default for Laravel's ID field
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('free_delivery')->default(false);
            $table->boolean('exempt_vat')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('has_variants')->default(false);
            $table->string('seo_url')->nullable();
            $table->string('short_url')->nullable();
            $table->text('seo_desc')->nullable();
            $table->text('seo_tags')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('tags')->nullable();
            $table->text('relevant_blog_url')->nullable();
            //$table->integer('shipping_duration_max')->nullable();
            //$table->integer('shipping_duration_min')->nullable();
            //$table->string('shipping_duration_metric')->nullable(); // e.g., days, hours
            $table->double('weight')->default(0);
            $table->double('price')->default(0); // May not be used.
            $table->integer('qty')->default(0); // May not be used.
            $table->enum('status', ['active', 'inactive', 'draft'])->default('active');
            // 
            $table->unsignedBigInteger('store_id'); 
            $table->unsignedBigInteger('category_id')->nullable(); 
            $table->unsignedBigInteger('sub_category_id')->nullable(); 
            // 
            $table->timestamps(); // Automatically adds created_at and updated_at
            // 
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade'); 
            $table->foreign('category_id')->references('id')->on('product_categories')->onDelete('set null');
            $table->foreign('sub_category_id')->references('id')->on('product_categories')->onDelete('set null');
            //
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
