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
        Schema::table('clubs', function (Blueprint $table): void {
            $table->enum('season_objective', [
                'avoid_relegation',
                'mid_table',
                'promotion',
                'title',
                'cup_run',
            ])->default('mid_table')->after('training_level');
            $table->index(['league_id', 'season_objective'], 'clubs_league_objective_idx');
        });

        Schema::table('competitions', function (Blueprint $table): void {
            $table->enum('scope', ['national', 'international'])
                ->nullable()
                ->after('type');
            $table->index(['type', 'scope', 'is_active'], 'competitions_type_scope_active_idx');
        });

        DB::table('competitions')
            ->where('type', 'cup')
            ->whereNull('scope')
            ->update([
                'scope' => DB::raw("CASE WHEN country_id IS NULL THEN 'international' ELSE 'national' END"),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table): void {
            $table->dropIndex('competitions_type_scope_active_idx');
            $table->dropColumn('scope');
        });

        Schema::table('clubs', function (Blueprint $table): void {
            $table->dropIndex('clubs_league_objective_idx');
            $table->dropColumn('season_objective');
        });
    }
};
