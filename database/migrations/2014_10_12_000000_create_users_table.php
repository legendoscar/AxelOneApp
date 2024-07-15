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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('username')->unique();
            $table->string('profile_url')->unique()->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('email_token',80)->unique()->nullable();
            $table->string('api_token',80)->unique()->nullable();
            $table->string('password');
            $table->string('password_token')->nullable();
            $table->string('verification_token')->nullable();
            $table->rememberToken();
            $table->string('phone_number')->nullable();
            $table->text('address')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->string('identification_type')->nullable(); // e.g. passport, driver's license, national ID
            $table->string('identification_number')->nullable(); // e.g. passport number, driver's license number, national ID number
            $table->date('date_of_birth')->nullable();
            $table->string('country_of_residence')->nullable();
            $table->string('country_of_citizenship')->nullable();
            $table->string('occupation')->nullable();
            $table->string('industry')->nullable();
            $table->boolean('is_politically_exposed')->default(false)->nullable();
            $table->string('income_source')->nullable();
            $table->integer('estimated_annual_income')->nullable(); // This can be stored as a string or integer based on your preference

            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
