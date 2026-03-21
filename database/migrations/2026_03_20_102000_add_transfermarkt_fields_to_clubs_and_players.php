<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->string('transfermarkt_id')->nullable()->after('league_id');
            $table->string('transfermarkt_url')->nullable()->after('transfermarkt_id');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->string('transfermarkt_id')->nullable()->after('club_id');
            $table->string('transfermarkt_url')->nullable()->after('transfermarkt_id');
        });
    }

    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn(['transfermarkt_id', 'transfermarkt_url']);
        });

        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn(['transfermarkt_id', 'transfermarkt_url']);
        });
    }
};
