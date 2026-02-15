<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('season_club_statistics', function (Blueprint $table) {
            $table->unsignedInteger('rank')->nullable()->after('club_id');
            $table->index(['competition_season_id', 'rank']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('season_club_statistics', function (Blueprint $table) {
            $table->dropIndex(['competition_season_id', 'rank']);
            $table->dropColumn('rank');
        });
    }
};
