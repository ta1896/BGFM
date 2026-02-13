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
        Schema::table('match_live_team_states', function (Blueprint $table): void {
            $table->foreignId('current_ball_carrier_player_id')
                ->nullable()
                ->after('phase')
                ->constrained('players')
                ->nullOnDelete();
            $table->foreignId('last_set_piece_taker_player_id')
                ->nullable()
                ->after('current_ball_carrier_player_id')
                ->constrained('players')
                ->nullOnDelete();
            $table->string('last_set_piece_type', 30)->nullable()->after('last_set_piece_taker_player_id');
            $table->unsignedTinyInteger('last_set_piece_minute')->nullable()->after('last_set_piece_type');
            $table->index(['match_id', 'current_ball_carrier_player_id'], 'mliveteam_match_ballcarrier_idx');
        });

        Schema::create('match_live_state_transitions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('club_id')->nullable()->constrained('clubs')->nullOnDelete();
            $table->unsignedTinyInteger('minute')->default(0);
            $table->unsignedTinyInteger('second')->default(0);
            $table->string('transition_type', 40);
            $table->string('from_phase', 30)->nullable();
            $table->string('to_phase', 30)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['match_id', 'minute', 'second'], 'mlivetrans_match_minute_second_idx');
            $table->index(['match_id', 'club_id', 'transition_type'], 'mlivetrans_match_club_type_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_live_state_transitions');

        Schema::table('match_live_team_states', function (Blueprint $table): void {
            $table->dropIndex('mliveteam_match_ballcarrier_idx');
            $table->dropConstrainedForeignId('last_set_piece_taker_player_id');
            $table->dropConstrainedForeignId('current_ball_carrier_player_id');
            $table->dropColumn(['last_set_piece_type', 'last_set_piece_minute']);
        });
    }
};

