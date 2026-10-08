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
            if (! Schema::hasColumn('elections_evote', 'vote_selection_mode')) {
                $table->string('vote_selection_mode', 20)->default('max')->after('max_votes_per_voter');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('elections_evote', function (Blueprint $table) {
            $table->dropColumn('vote_selection_mode');
        });
    }
};
