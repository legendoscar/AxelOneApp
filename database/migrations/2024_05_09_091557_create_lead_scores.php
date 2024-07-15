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
        Schema::create('lead_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('organization_id');
            // $table->foreign('org_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->string('criteria'); //company size //industry
            $table->string('name'); //1-10 employees //education
            $table->string('value'); //1 point //2 point
            $table->text('description');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_scores');
    }
};
