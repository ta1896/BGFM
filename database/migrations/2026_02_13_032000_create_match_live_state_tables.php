<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_live_team_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->unsignedInteger('possession_seconds')->default(0);
            $table->unsignedSmallInteger('actions_count')->default(0);
            $table->unsignedSmallInteger('dangerous_attacks')->default(0);
            $table->unsignedSmallInteger('pass_attempts')->default(0);
            $table->unsignedSmallInteger('pass_completions')->default(0);
            $table->unsignedSmallInteger('tackle_attempts')->default(0);
            $table->unsignedSmallInteger('tackle_won')->default(0);
            $table->unsignedSmallInteger('fouls_committed')->default(0);
            $table->unsignedSmallInteger('corners_won')->default(0);
            $table->unsignedSmallInteger('shots')->default(0);
            $table->unsignedSmallInteger('shots_on_target')->default(0);
            $table->decimal('expected_goals', 6, 2)->default(0);
            $table->unsignedTinyInteger('yellow_cards')->default(0);
            $table->unsignedTinyInteger('red_cards')->default(0);
            $table->unsignedTinyInteger('substitutions_used')->default(0);
            $table->unsignedTinyInteger('tactical_changes_count')->default(0);
            $table->unsignedTinyInteger('last_tactical_change_minute')->nullable();
            $table->unsignedTinyInteger('last_substitution_minute')->nullable();
            $table->string('tactical_style', 20)->default('balanced');
            $table->string('phase', 30)->nullable();
            $table->timestamps();

            $table->unique(['match_id', 'club_id'], 'mliveteam_match_club_unique');
            $table->index(['match_id', 'club_id'], 'mliveteam_match_club_idx');
        });

        Schema::create('match_live_player_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->string('slot', 20)->nullable();
            $table->boolean('is_on_pitch')->default(false);
            $table->boolean('is_sent_off')->default(false);
            $table->boolean('is_injured')->default(false);
            $table->decimal('fit_factor', 5, 2)->default(1.00);
            $table->unsignedTinyInteger('minutes_played')->default(0);
            $table->unsignedSmallInteger('ball_contacts')->default(0);
            $table->unsignedSmallInteger('pass_attempts')->default(0);
            $table->unsignedSmallInteger('pass_completions')->default(0);
            $table->unsignedSmallInteger('tackle_attempts')->default(0);
            $table->unsignedSmallInteger('tackle_won')->default(0);
            $table->unsignedSmallInteger('fouls_committed')->default(0);
            $table->unsignedSmallInteger('fouls_suffered')->default(0);
            $table->unsignedSmallInteger('shots')->default(0);
            $table->unsignedSmallInteger('shots_on_target')->default(0);
            $table->unsignedSmallInteger('goals')->default(0);
            $table->unsignedSmallInteger('assists')->default(0);
            $table->unsignedTinyInteger('yellow_cards')->default(0);
            $table->unsignedTinyInteger('red_cards')->default(0);
            $table->unsignedSmallInteger('saves')->default(0);
            $table->timestamps();

            $table->unique(['match_id', 'player_id'], 'mliveplayer_match_player_unique');
            $table->index(['match_id', 'club_id', 'is_on_pitch'], 'mliveplayer_match_club_pitch_idx');
        });

        Schema::create('match_live_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->unsignedTinyInteger('minute');
            $table->unsignedTinyInteger('second')->default(0);
            $table->unsignedSmallInteger('sequence')->default(0);
            $table->foreignId('club_id')->nullable()->constrained('clubs')->nullOnDelete();
            $table->foreignId('player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('opponent_player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->string('action_type', 40);
            $table->string('outcome', 40)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['match_id', 'minute', 'sequence'], 'mliveaction_match_minute_seq_idx');
            $table->index(['match_id', 'club_id', 'action_type'], 'mliveaction_match_club_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_live_actions');
        Schema::dropIfExists('match_live_player_states');
        Schema::dropIfExists('match_live_team_states');
    }
};
