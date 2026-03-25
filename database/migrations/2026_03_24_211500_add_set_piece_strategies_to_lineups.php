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
        Schema::table('lineups', function (Blueprint $table) {
            $table->string('corner_marking_strategy', 20)->default('zonal')->after('corner_right_taker_player_id');
            $table->string('free_kick_marking_strategy', 20)->default('zonal')->after('corner_marking_strategy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lineups', function (Blueprint $table) {
            $table->dropColumn(['corner_marking_strategy', 'free_kick_marking_strategy']);
        });
    }
};
