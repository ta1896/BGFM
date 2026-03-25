<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_player_stats', function (Blueprint $table) {
            if (!Schema::hasColumn('match_player_stats', 'shots_on_target')) {
                $table->integer('shots_on_target')->default(0)->after('shots');
            }
        });
    }

    public function down(): void
    {
        Schema::table('match_player_stats', function (Blueprint $table) {
            $table->dropColumn('shots_on_target');
        });
    }
};
