<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('season_club_statistics', function (Blueprint $table) {
            $table->unsignedSmallInteger('yellow_cards')->default(0)->after('form_last5');
            $table->unsignedSmallInteger('red_cards')->default(0)->after('yellow_cards');
        });
    }

    public function down(): void
    {
        Schema::table('season_club_statistics', function (Blueprint $table) {
            $table->dropColumn(['yellow_cards', 'red_cards']);
        });
    }
};
