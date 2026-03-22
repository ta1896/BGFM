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
        Schema::table('players', function (Blueprint $table) {
            DB::statement("ALTER TABLE players MODIFY COLUMN position ENUM('TW', 'LV', 'IV', 'RV', 'LWB', 'RWB', 'LM', 'ZM', 'RM', 'DM', 'OM', 'LAM', 'ZOM', 'RAM', 'LS', 'MS', 'RS', 'LW', 'RW', 'ST', 'HS')");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            DB::statement("ALTER TABLE players MODIFY COLUMN position ENUM('TW', 'LV', 'IV', 'RV', 'LWB', 'RWB', 'LM', 'ZM', 'RM', 'DM', 'OM', 'LAM', 'ZOM', 'RAM', 'LS', 'MS', 'RS', 'LW', 'RW', 'ST')");
        });
    }
};
