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
        if (! Schema::hasTable('simulation_votes')) {
            Schema::create('simulation_votes', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('election_id')->constrained('elections_evote')->cascadeOnDelete();
                $table->foreignUuid('candidate_id')->constrained('candidates_evote')->cascadeOnDelete();
                $table->integer('stage')->default(1);
                $table->string('voter_alias')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simulation_votes');
    }
};
