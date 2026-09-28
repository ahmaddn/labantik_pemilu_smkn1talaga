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
            if (! Schema::hasColumn('elections_evote', 'stage_schedules')) {
                $table->json('stage_schedules')->nullable()->after('total_stages');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('elections_evote', function (Blueprint $table) {
            $table->dropColumn('stage_schedules');
        });
    }
};
