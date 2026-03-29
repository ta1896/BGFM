<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Speeds up action queries during live match (sorted by match_id + minute)
        Schema::table('match_live_actions', function (Blueprint $table) {
            $table->index(['match_id', 'minute'], 'mla_match_minute_idx');
        });

        // Speeds up player state lookups during lineup sync and substitutions
        Schema::table('match_live_player_states', function (Blueprint $table) {
            $table->index(['match_id', 'club_id', 'player_id'], 'mlps_match_club_player_idx');
        });

        // Speeds up fetching live/scheduled matches across the system
        Schema::table('matches', function (Blueprint $table) {
            $table->index(['status', 'type'], 'matches_status_type_idx');
        });
    }

    public function down(): void
    {
        Schema::table('match_live_actions', function (Blueprint $table) {
            $table->dropIndex('mla_match_minute_idx');
        });

        Schema::table('match_live_player_states', function (Blueprint $table) {
            $table->dropIndex('mlps_match_club_player_idx');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->dropIndex('matches_status_type_idx');
        });
    }
};
