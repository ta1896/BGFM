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
        Schema::table('competition_seasons', function (Blueprint $table) {
            $table->foreignId('league_winner_club_id')->nullable()->constrained('clubs')->nullOnDelete();
            $table->foreignId('national_cup_winner_club_id')->nullable()->constrained('clubs')->nullOnDelete();
            $table->foreignId('intl_cup_winner_club_id')->nullable()->constrained('clubs')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competition_seasons', function (Blueprint $table) {
            $table->dropForeign(['league_winner_club_id']);
            $table->dropForeign(['national_cup_winner_club_id']);
            $table->dropForeign(['intl_cup_winner_club_id']);
            $table->dropColumn(['league_winner_club_id', 'national_cup_winner_club_id', 'intl_cup_winner_club_id']);
        });
    }
};
