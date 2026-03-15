<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_injuries', function (Blueprint $table): void {
            if (!Schema::hasColumn('player_injuries', 'availability_status')) {
                $table->string('availability_status')->default('unavailable')->after('return_phase');
            }

            if (!Schema::hasColumn('player_injuries', 'cleared_at')) {
                $table->timestamp('cleared_at')->nullable()->after('actual_return_at');
            }
        });

        Schema::table('scouting_watchlists', function (Blueprint $table): void {
            if (!Schema::hasColumn('scouting_watchlists', 'focus')) {
                $table->string('focus')->default('general')->after('status');
            }

            if (!Schema::hasColumn('scouting_watchlists', 'progress')) {
                $table->unsignedTinyInteger('progress')->default(0)->after('focus');
            }

            if (!Schema::hasColumn('scouting_watchlists', 'reports_requested')) {
                $table->unsignedInteger('reports_requested')->default(0)->after('progress');
            }

            if (!Schema::hasColumn('scouting_watchlists', 'last_scouted_at')) {
                $table->timestamp('last_scouted_at')->nullable()->after('reports_requested');
            }

            if (!Schema::hasColumn('scouting_watchlists', 'next_report_due_at')) {
                $table->timestamp('next_report_due_at')->nullable()->after('last_scouted_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('player_injuries', function (Blueprint $table): void {
            foreach (['availability_status', 'cleared_at'] as $column) {
                if (Schema::hasColumn('player_injuries', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('scouting_watchlists', function (Blueprint $table): void {
            foreach (['focus', 'progress', 'reports_requested', 'last_scouted_at', 'next_report_due_at'] as $column) {
                if (Schema::hasColumn('scouting_watchlists', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
