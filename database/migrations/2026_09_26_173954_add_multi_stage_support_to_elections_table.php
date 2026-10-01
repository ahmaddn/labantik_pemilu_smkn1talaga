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
        Schema::table('elections_evote', function (Blueprint $table) {
            if (! Schema::hasColumn('elections_evote', 'is_multi_stage')) {
                $table->boolean('is_multi_stage')->default(false)->after('is_published');
            }
            if (! Schema::hasColumn('elections_evote', 'current_stage')) {
                $table->integer('current_stage')->default(1)->after('is_multi_stage');
            }
            if (! Schema::hasColumn('elections_evote', 'total_stages')) {
                $table->integer('total_stages')->default(1)->after('current_stage');
            }
        });

        Schema::table('candidates_evote', function (Blueprint $table) {
            if (! Schema::hasColumn('candidates_evote', 'is_qualified')) {
                $table->boolean('is_qualified')->default(true)->after('vision_mission');
            }
            if (! Schema::hasColumn('candidates_evote', 'eliminated_at_stage')) {
                $table->integer('eliminated_at_stage')->nullable()->after('is_qualified');
            }
        });

        Schema::table('votes_evote', function (Blueprint $table) {
            if (! Schema::hasColumn('votes_evote', 'stage_number')) {
                $table->integer('stage_number')->default(1)->after('candidate_id');
            }
        });

        Schema::table('voter_accesses_evote', function (Blueprint $table) {
            if (! Schema::hasColumn('voter_accesses_evote', 'stage_number')) {
                $table->integer('stage_number')->default(1)->after('election_id');
            }
        });

        // Add unique index safely if it doesn't exist
        $indexes = collect(DB::select("SHOW INDEX FROM `voter_accesses_evote` WHERE Key_name = 'unique_voter_election_stage'"));
        if ($indexes->isEmpty()) {
            Schema::table('voter_accesses_evote', function (Blueprint $table) {
                $table->unique(['election_id', 'user_id', 'stage_number'], 'unique_voter_election_stage');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('voter_accesses_evote', function (Blueprint $table) {
            $table->dropUnique('unique_voter_election_stage');
            $table->unique(['election_id', 'user_id'], 'unique_voter_election');
            $table->dropColumn('stage_number');
        });

        Schema::table('votes_evote', function (Blueprint $table) {
            $table->dropColumn('stage_number');
        });

        Schema::table('candidates_evote', function (Blueprint $table) {
            $table->dropColumn(['is_qualified', 'eliminated_at_stage']);
        });

        Schema::table('elections_evote', function (Blueprint $table) {
            $table->dropColumn(['is_multi_stage', 'current_stage', 'total_stages']);
        });
    }
};
