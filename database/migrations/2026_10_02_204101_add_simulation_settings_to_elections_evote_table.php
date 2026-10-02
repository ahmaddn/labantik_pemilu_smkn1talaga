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
            if (! Schema::hasColumn('elections_evote', 'is_simulation')) {
                $table->boolean('is_simulation')->default(false)->after('is_multi_stage');
            }
            if (! Schema::hasColumn('elections_evote', 'simulation_start_at')) {
                $table->timestamp('simulation_start_at')->nullable()->after('end_at');
            }
            if (! Schema::hasColumn('elections_evote', 'simulation_end_at')) {
                $table->timestamp('simulation_end_at')->nullable()->after('simulation_start_at');
            }
        });

        if (Schema::hasTable('simulation_votes')) {
            Schema::table('simulation_votes', function (Blueprint $table) {
                if (! Schema::hasColumn('simulation_votes', 'user_id')) {
                    $table->char('user_id', 36)->nullable()->after('election_id');
                    if (Schema::hasTable('core_users')) {
                        $table->foreign('user_id')->references('id')->on('core_users')->nullOnDelete();
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('simulation_votes')) {
            Schema::table('simulation_votes', function (Blueprint $table) {
                if (Schema::hasColumn('simulation_votes', 'user_id')) {
                    $table->dropForeign(['user_id']);
                    $table->dropColumn('user_id');
                }
            });
        }

        Schema::table('elections_evote', function (Blueprint $table) {
            $table->dropColumn(['is_simulation', 'simulation_start_at', 'simulation_end_at']);
        });
    }
};
