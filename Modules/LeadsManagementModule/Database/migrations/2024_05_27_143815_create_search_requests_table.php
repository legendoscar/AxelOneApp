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
        Schema::create('search_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // To store who searched
            $table->string('search_term'); // To store what was searched
            $table->ipAddress('ip_address')->nullable(); // To store the IP address
            $table->string('user_agent')->nullable(); // To store the user agent string
            $table->json('search_filters')->nullable(); // To store any search filters as JSON
            $table->json('org_matched')->nullable();
            $table->integer('results_count')->nullable(); // To store the number of results returned
            $table->float('duration', 8, 3)->nullable(); // To store the search duration in seconds
            $table->timestamps(); // To store the time of the search
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_requests');
    }
};
