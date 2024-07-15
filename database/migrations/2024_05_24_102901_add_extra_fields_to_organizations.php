<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->after('linkedin_url', function (Blueprint $table) {
                $table->json('location')->nullable();
                $table->json('products')->nullable();
                $table->json('services')->nullable();
                $table->json('business_hours')->nullable();
                $table->json('website_social_media')->nullable();
                $table->json('contact_info')->nullable();
                $table->json('reviews_ratings')->nullable();
                $table->json('pricing')->nullable();
                $table->json('certifications_accreditations')->nullable();
                $table->json('languages_spoken')->nullable();
                $table->json('payment_methods')->nullable();
                $table->json('nearby_landmarks')->nullable();
                $table->json('parking_info')->nullable();
                $table->json('pet_policy')->nullable();
                $table->json('dress_code')->nullable();
                $table->json('special_instructions')->nullable();
                $table->json('accessibility')->nullable();
                $table->json('events_promotions')->nullable();
                $table->json('cancellation_policy')->nullable();
                $table->json('environmental_practices')->nullable();
                $table->json('awards_nominations')->nullable();
                $table->json('user_generated_contents')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            //
        });
    }
};
