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
        Schema::table('elections_evote', function (Blueprint $table) {
            $table->unsignedInteger('max_votes_per_voter')->default(1)->after('is_multi_stage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('elections_evote', function (Blueprint $table) {
            $table->dropColumn('max_votes_per_voter');
        });
    }
};
