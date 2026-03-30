<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_live_team_states', function (Blueprint $table) {
            $table->string('corner_strategy', 20)->nullable()->after('last_set_piece_minute');
            $table->string('free_kick_strategy', 20)->nullable()->after('corner_strategy');
        });
    }

    public function down(): void
    {
        Schema::table('match_live_team_states', function (Blueprint $table) {
            $table->dropColumn(['corner_strategy', 'free_kick_strategy']);
        });
    }
};
