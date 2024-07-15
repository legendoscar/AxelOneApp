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
        Schema::table('search_leads', function (Blueprint $table) {
            if (!Schema::hasColumn('search_leads', 'status')) {
                $table->string('status')->default('new')->after('data'); // Lead status
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('search_leads', function (Blueprint $table) {

        });
    }
};
