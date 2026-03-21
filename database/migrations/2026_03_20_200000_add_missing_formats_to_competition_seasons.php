<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL specific ENUM update
        DB::statement("ALTER TABLE competition_seasons MODIFY COLUMN format ENUM('round_robin', 'knockout', 'groups_knockout', 'league_10', 'league_12', 'league_14', 'league_16', 'league_18', 'league_20', 'league_22') DEFAULT 'round_robin'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE competition_seasons MODIFY COLUMN format ENUM('round_robin', 'knockout', 'groups_knockout') DEFAULT 'round_robin'");
    }
};
