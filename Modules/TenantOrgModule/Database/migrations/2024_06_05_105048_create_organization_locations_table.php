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
        Schema::create('organization_locations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('organization_id');
            $table->string('location_name')->nullable();
            $table->string('country');
            $table->string('state');
            $table->string('city');
            $table->string('address')->nullable();
            $table->string('zipcode')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('long', 10, 7)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_locations');
    }
};
