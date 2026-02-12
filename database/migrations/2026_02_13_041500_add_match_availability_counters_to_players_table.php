<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->unsignedTinyInteger('injury_matches_remaining')->default(0)->after('last_training_at');
            $table->unsignedTinyInteger('suspension_matches_remaining')->default(0)->after('injury_matches_remaining');

            $table->index(['club_id', 'injury_matches_remaining'], 'players_club_injury_remaining_idx');
            $table->index(['club_id', 'suspension_matches_remaining'], 'players_club_suspension_remaining_idx');
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropIndex('players_club_injury_remaining_idx');
            $table->dropIndex('players_club_suspension_remaining_idx');
            $table->dropColumn(['injury_matches_remaining', 'suspension_matches_remaining']);
        });
    }
};
