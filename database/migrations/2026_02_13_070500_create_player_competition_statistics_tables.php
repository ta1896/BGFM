<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_season_competition_statistics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('season_id')->constrained('seasons')->cascadeOnDelete();
            $table->enum('competition_context', ['league', 'cup_national', 'cup_international', 'friendly']);
            $table->unsignedSmallInteger('appearances')->default(0);
            $table->unsignedSmallInteger('minutes_played')->default(0);
            $table->unsignedSmallInteger('goals')->default(0);
            $table->unsignedSmallInteger('assists')->default(0);
            $table->unsignedSmallInteger('yellow_cards')->default(0);
            $table->unsignedSmallInteger('red_cards')->default(0);
            $table->timestamps();

            $table->unique(
                ['player_id', 'season_id', 'competition_context'],
                'pl_season_comp_stats_unique'
            );
            $table->index(
                ['season_id', 'competition_context', 'goals', 'assists'],
                'pl_season_comp_stats_leader_idx'
            );
        });

        Schema::create('player_career_competition_statistics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->enum('competition_context', ['league', 'cup_national', 'cup_international', 'friendly']);
            $table->unsignedSmallInteger('appearances')->default(0);
            $table->unsignedSmallInteger('minutes_played')->default(0);
            $table->unsignedSmallInteger('goals')->default(0);
            $table->unsignedSmallInteger('assists')->default(0);
            $table->unsignedSmallInteger('yellow_cards')->default(0);
            $table->unsignedSmallInteger('red_cards')->default(0);
            $table->timestamps();

            $table->unique(
                ['player_id', 'competition_context'],
                'pl_career_comp_stats_unique'
            );
            $table->index(
                ['competition_context', 'goals', 'assists'],
                'pl_career_comp_stats_leader_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_career_competition_statistics');
        Schema::dropIfExists('player_season_competition_statistics');
    }
};
