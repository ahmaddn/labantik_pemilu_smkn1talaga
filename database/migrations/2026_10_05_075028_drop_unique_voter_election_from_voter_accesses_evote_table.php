<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $indexes = collect(DB::select("SHOW INDEX FROM `voter_accesses_evote` WHERE Key_name = 'unique_voter_election'"));
        if ($indexes->isNotEmpty()) {
            Schema::table('voter_accesses_evote', function (Blueprint $table) {
                $table->dropUnique('unique_voter_election');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexes = collect(DB::select("SHOW INDEX FROM `voter_accesses_evote` WHERE Key_name = 'unique_voter_election'"));
        if ($indexes->isEmpty()) {
            Schema::table('voter_accesses_evote', function (Blueprint $table) {
                $table->unique(['election_id', 'user_id'], 'unique_voter_election');
            });
        }
    }
};
