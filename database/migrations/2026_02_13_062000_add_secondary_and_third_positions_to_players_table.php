<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->string('position_second', 8)->nullable()->after('position');
            $table->string('position_third', 8)->nullable()->after('position_second');

            $table->index(['position', 'position_second', 'position_third'], 'players_position_profile_idx');
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropIndex('players_position_profile_idx');
            $table->dropColumn(['position_second', 'position_third']);
        });
    }
};
