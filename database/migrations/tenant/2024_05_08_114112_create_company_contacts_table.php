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
        /* These are contacts associated with the companies created by users */

        Schema::create('company_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('firstname');
            $table->string('lastname');

            $table->unsignedBigInteger('company_id')->nullable();
            // $table->foreign('company_id')->references('id')->on('companies');

            $table->string('email')->unique();
            $table->string('phone_number')->nullable();
            $table->text('address')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('country_of_residence')->nullable();
            $table->string('country_of_citizenship')->nullable();
            $table->string('occupation')->nullable();
            $table->string('industry')->nullable();
            $table->boolean('is_politically_exposed')->default(false);
            $table->string('income_source')->nullable();
            $table->integer('estimated_annual_income')->nullable(); // This can be stored as a string or integer based on your preference
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('twitter_x_url')->nullable();
            $table->string('linkedin_url')->nullable();


            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_contacts');
    }
};
