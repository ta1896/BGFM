<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_player_stats', function (Blueprint $table) {
            $table->decimal('xg', 5, 2)->default(0)->after('rating');
            $table->decimal('xgot', 5, 2)->default(0)->after('xg');
            $table->integer('passes_attempted')->default(0)->after('passes_completed');
            $table->integer('long_balls_completed')->default(0)->after('passes_attempted');
            $table->integer('long_balls_attempted')->default(0)->after('long_balls_completed');
            $table->integer('chances_created')->default(0)->after('assists');
            $table->integer('big_chances_created')->default(0)->after('chances_created');
            $table->integer('dribbles_completed')->default(0)->after('shots');
            $table->integer('dribbles_attempted')->default(0)->after('dribbles_completed');
            $table->integer('duels_won')->default(0)->after('tackles_lost');
            $table->integer('duels_total')->default(0)->after('duels_won');
            $table->integer('aerials_won')->default(0)->after('duels_total');
            $table->integer('aerials_total')->default(0)->after('aerials_won');
            $table->integer('interceptions')->default(0)->after('tackles_lost');
            $table->integer('recoveries')->default(0)->after('interceptions');
            $table->integer('clearances')->default(0)->after('recoveries');
        });

        Schema::table('match_live_actions', function (Blueprint $table) {
            $table->decimal('xgot', 5, 2)->nullable()->after('xg');
        });
    }

    public function down(): void
    {
        Schema::table('match_player_stats', function (Blueprint $table) {
            $table->dropColumn([
                'xg', 'xgot', 'passes_attempted', 'long_balls_completed', 'long_balls_attempted',
                'chances_created', 'big_chances_created', 'dribbles_completed', 'dribbles_attempted',
                'duels_won', 'duels_total', 'aerials_won', 'aerials_total', 'interceptions', 'recoveries', 'clearances'
            ]);
        });

        Schema::table('match_live_actions', function (Blueprint $table) {
            $table->dropColumn('xgot');
        });
    }
};
