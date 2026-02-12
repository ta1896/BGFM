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
        Schema::table('lineups', function (Blueprint $table): void {
            $table->enum('attack_focus', ['left', 'center', 'right'])
                ->default('center')
                ->after('tactical_style');
            $table->foreignId('penalty_taker_player_id')
                ->nullable()
                ->after('attack_focus')
                ->constrained('players')
                ->nullOnDelete();
            $table->foreignId('free_kick_taker_player_id')
                ->nullable()
                ->after('penalty_taker_player_id')
                ->constrained('players')
                ->nullOnDelete();
            $table->foreignId('corner_left_taker_player_id')
                ->nullable()
                ->after('free_kick_taker_player_id')
                ->constrained('players')
                ->nullOnDelete();
            $table->foreignId('corner_right_taker_player_id')
                ->nullable()
                ->after('corner_left_taker_player_id')
                ->constrained('players')
                ->nullOnDelete();
            $table->index(['match_id', 'club_id', 'is_template'], 'lineups_match_club_template_idx');
        });

        Schema::table('lineup_player', function (Blueprint $table): void {
            $table->boolean('is_bench')->default(false)->after('is_set_piece_taker');
            $table->unsignedTinyInteger('bench_order')->nullable()->after('is_bench');
            $table->index(['lineup_id', 'is_bench', 'sort_order'], 'lineupplayer_lineup_bench_sort_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lineup_player', function (Blueprint $table): void {
            $table->dropIndex('lineupplayer_lineup_bench_sort_idx');
            $table->dropColumn(['is_bench', 'bench_order']);
        });

        Schema::table('lineups', function (Blueprint $table): void {
            $table->dropIndex('lineups_match_club_template_idx');
            $table->dropConstrainedForeignId('penalty_taker_player_id');
            $table->dropConstrainedForeignId('free_kick_taker_player_id');
            $table->dropConstrainedForeignId('corner_left_taker_player_id');
            $table->dropConstrainedForeignId('corner_right_taker_player_id');
            $table->dropColumn('attack_focus');
        });
    }
};
