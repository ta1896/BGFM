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
        Schema::table('clubs', function (Blueprint $table): void {
            $table->foreignId('captain_player_id')
                ->nullable()
                ->after('season_objective')
                ->constrained('players')
                ->nullOnDelete();
            $table->foreignId('vice_captain_player_id')
                ->nullable()
                ->after('captain_player_id')
                ->constrained('players')
                ->nullOnDelete();

            $table->index(['captain_player_id', 'vice_captain_player_id'], 'clubs_role_players_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table): void {
            $table->dropIndex('clubs_role_players_idx');
            $table->dropConstrainedForeignId('vice_captain_player_id');
            $table->dropConstrainedForeignId('captain_player_id');
        });
    }
};

