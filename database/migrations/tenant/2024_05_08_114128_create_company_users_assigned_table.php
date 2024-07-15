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
        /* These are companies assigned to users who belong to an organization */

        Schema::create('company_users_assigned', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id');
            $table->primary(['user_id', 'company_id']);
            // $table->foreign('user_id')->references('id')->on('users');
            // $table->foreign('company_id')->references('id')->on('companies');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_users_assigned');
    }
};
