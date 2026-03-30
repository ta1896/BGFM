<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->enum('personality_type', [
                'leader',
                'temperamental',
                'team_player',
                'silent_pro',
                'maverick',
                'youngster',
            ])->nullable()->after('player_style');
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('personality_type');
        });
    }
};
