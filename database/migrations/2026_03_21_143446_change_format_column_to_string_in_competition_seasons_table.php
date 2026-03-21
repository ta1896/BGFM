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
        Schema::table('competition_seasons', function (Blueprint $table) {
            $table->string('format')->default('round_robin')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competition_seasons', function (Blueprint $table) {
            $table->enum('format', ['round_robin', 'knockout', 'groups_knockout', 'league_10', 'league_12', 'league_14', 'league_16', 'league_18', 'league_20', 'league_22'])->default('round_robin')->change();
        });
    }
};
