<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scouting_watchlists', function (Blueprint $table): void {
            if (!Schema::hasColumn('scouting_watchlists', 'scout_level')) {
                $table->string('scout_level', 16)->default('experienced')->after('focus');
            }

            if (!Schema::hasColumn('scouting_watchlists', 'scout_region')) {
                $table->string('scout_region', 16)->default('domestic')->after('scout_level');
            }

            if (!Schema::hasColumn('scouting_watchlists', 'scout_type')) {
                $table->string('scout_type', 16)->default('live')->after('scout_region');
            }

            if (!Schema::hasColumn('scouting_watchlists', 'mission_days_left')) {
                $table->unsignedSmallInteger('mission_days_left')->default(0)->after('reports_requested');
            }

            if (!Schema::hasColumn('scouting_watchlists', 'last_mission_cost')) {
                $table->decimal('last_mission_cost', 10, 2)->default(0)->after('mission_days_left');
            }
        });
    }

    public function down(): void
    {
        Schema::table('scouting_watchlists', function (Blueprint $table): void {
            foreach (['scout_level', 'scout_region', 'scout_type', 'mission_days_left', 'last_mission_cost'] as $column) {
                if (Schema::hasColumn('scouting_watchlists', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
