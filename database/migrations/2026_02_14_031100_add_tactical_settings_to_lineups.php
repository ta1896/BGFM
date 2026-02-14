<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::table('lineups', function (Blueprint $table): void {
            // Replace tactical_style with mentality
            $table->string('mentality', 20)->default('normal')->after('formation');
            $table->string('aggression', 20)->default('normal')->after('mentality');
            $table->string('line_height', 20)->default('normal')->after('aggression');
            $table->boolean('offside_trap')->default(false)->after('line_height');
            $table->boolean('time_wasting')->default(false)->after('offside_trap');

            // New free kick taker columns
            $table->foreignId('free_kick_near_player_id')
                ->nullable()
                ->after('free_kick_taker_player_id')
                ->constrained('players')
                ->nullOnDelete();
            $table->foreignId('free_kick_far_player_id')
                ->nullable()
                ->after('free_kick_near_player_id')
                ->constrained('players')
                ->nullOnDelete();
        });

        // Migrate tactical_style data to mentality
        DB::table('lineups')->where('tactical_style', 'balanced')->update(['mentality' => 'normal']);
        DB::table('lineups')->where('tactical_style', 'offensive')->update(['mentality' => 'offensive']);
        DB::table('lineups')->where('tactical_style', 'defensive')->update(['mentality' => 'defensive']);
        DB::table('lineups')->where('tactical_style', 'counter')->update(['mentality' => 'counter']);

        Schema::table('lineups', function (Blueprint $table): void {
            $table->dropColumn('tactical_style');
        // Rename old free_kick_taker to free_kick_near (keep data)
        });
    }

    public function down(): void
    {
        Schema::table('lineups', function (Blueprint $table): void {
            $table->string('tactical_style', 20)->nullable()->after('formation');
        });

        DB::table('lineups')->where('mentality', 'normal')->update(['tactical_style' => 'balanced']);
        DB::table('lineups')->where('mentality', 'offensive')->update(['tactical_style' => 'offensive']);
        DB::table('lineups')->where('mentality', 'defensive')->update(['tactical_style' => 'defensive']);
        DB::table('lineups')->where('mentality', 'counter')->update(['tactical_style' => 'counter']);

        Schema::table('lineups', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('free_kick_near_player_id');
            $table->dropConstrainedForeignId('free_kick_far_player_id');
            $table->dropColumn(['mentality', 'aggression', 'line_height', 'offside_trap', 'time_wasting']);
        });
    }
};
