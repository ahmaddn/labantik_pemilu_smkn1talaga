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
        // 1. Elections Table (elections_evote)
        if (! Schema::hasTable('elections_evote')) {
            Schema::create('elections_evote', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->char('created_by', 36);
                $table->string('title');
                $table->text('description')->nullable();
                $table->enum('type', ['osis', 'class_president', 'other'])->default('osis');
                $table->enum('target_voter', ['all', 'student', 'teacher'])->default('all');
                $table->string('academic_year', 10)->nullable();
                $table->char('class_id', 36)->nullable();
                $table->dateTime('start_at');
                $table->dateTime('end_at');
                $table->boolean('is_published')->default(false);
                $table->timestamps();

                if (Schema::hasTable('core_users')) {
                    $table->foreign('created_by')->references('id')->on('core_users')->onDelete('cascade');
                }
                if (Schema::hasTable('ref_classes')) {
                    $table->foreign('class_id')->references('id')->on('ref_classes')->onDelete('set null');
                }
            });
        }

        // 2. Candidates Table (candidates_evote)
        if (! Schema::hasTable('candidates_evote')) {
            Schema::create('candidates_evote', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('election_id')->constrained('elections_evote')->onDelete('cascade');
                $table->integer('candidate_number');
                $table->string('chairman_name');
                $table->string('vice_chairman_name')->nullable();
                $table->string('photo')->nullable();
                $table->text('vision_mission')->nullable();
                $table->timestamps();
            });
        }

        // 3. Voter Access Table (voter_accesses_evote)
        if (! Schema::hasTable('voter_accesses_evote')) {
            Schema::create('voter_accesses_evote', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->char('user_id', 36);
                $table->foreignUuid('election_id')->constrained('elections_evote')->onDelete('cascade');
                $table->boolean('is_voted')->default(false);
                $table->timestamp('voted_at')->nullable();
                $table->timestamps();

                $table->unique(['election_id', 'user_id'], 'unique_voter_election');
                $table->index(['user_id', 'is_voted']);
                if (Schema::hasTable('core_users')) {
                    $table->foreign('user_id')->references('id')->on('core_users')->onDelete('cascade');
                }
            });
        }

        // 4. Votes Table (votes_evote) - Anonymous Ballots
        if (! Schema::hasTable('votes_evote')) {
            Schema::create('votes_evote', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('election_id')->constrained('elections_evote')->onDelete('cascade');
                $table->foreignUuid('candidate_id')->constrained('candidates_evote')->onDelete('cascade');
                $table->timestamp('created_at')->useCurrent();

                $table->index(['election_id', 'candidate_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes_evote');
        Schema::dropIfExists('voter_accesses_evote');
        Schema::dropIfExists('candidates_evote');
        Schema::dropIfExists('elections_evote');
    }
};
