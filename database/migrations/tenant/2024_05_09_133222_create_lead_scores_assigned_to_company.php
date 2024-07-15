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
        Schema::create('lead_scores_assigned_to_company', function (Blueprint $table) {
            $table->unsignedBigInteger('lead_scores_id');
            $table->unsignedBigInteger('company_leads_id');
            $table->primary(['lead_scores_id', 'company_leads_id']);

            // $table->foreign('lead_scores_id')->references('id')->on('lead_scores');
            // $table->foreign('company_leads_id')->references('id')->on('company_leads');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_scores_assigned_to_company');
    }
};
