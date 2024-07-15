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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            // $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedBigInteger('organization_id');
            // $table->foreign('organization_id')->references('id')->on('organizations');

            $table->string('title');
            $table->text('content');

            // Column for storing post slug
            $table->string('slug')->unique();

            // Column for storing post excerpt
            $table->text('excerpt')->nullable();

            // Column for storing published status
            $table->boolean('is_published')->default(false);

            // Column for storing post category
            $table->unsignedBigInteger('category_id');
            // $table->foreign('category_id')->references('id')->on('posts_categories');



            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
