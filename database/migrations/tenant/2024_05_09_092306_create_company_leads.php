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
        Schema::create('company_leads', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('owner_id');
            // $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('lead_company_id');
            // $table->foreign('lead_company_id')->references('id')->on('companies')->onDelete('cascade');

            $table->unsignedBigInteger('lead_contact_id');
            // $table->foreign('lead_contact_id')->references('id')->on('company_contacts')->onDelete('cascade');

            $table->unsignedBigInteger('sales_funnel')->nullable();
            // $table->foreign('sales_funnel')->references('id')->on('sales_funnel')->onDelete('cascade');

            // $table->unsignedBigInteger('lead_score');
            // $table->foreign('lead_score')->references('id')->on('lead_scores')->onDelete('cascade');

            $table->string('expected_close_date')->nullable();
            $table->string('probability_of_conversion')->nullable();
            $table->string('priority_level')->nullable();
            $table->string('preferred_comm_method')->nullable();
            $table->text('goals_and_challenges')->nullable();


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
        Schema::dropIfExists('company_leads');
    }
};
