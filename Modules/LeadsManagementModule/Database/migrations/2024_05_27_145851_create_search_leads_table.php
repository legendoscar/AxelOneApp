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
        Schema::create('search_leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('search_request_id'); // Reference to the search request
            $table->unsignedBigInteger('user_id')->nullable(); // Reference to the user who searched
            $table->json('data')->nullable(); // Additional lead data
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_leads');
    }
};
