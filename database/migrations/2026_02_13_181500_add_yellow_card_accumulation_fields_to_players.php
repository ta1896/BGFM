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
        Schema::table('players', function (Blueprint $table): void {
            $table->unsignedTinyInteger('yellow_cards_league_accumulated')
                ->default(0)
                ->after('suspension_friendly_remaining');
            $table->unsignedTinyInteger('yellow_cards_cup_national_accumulated')
                ->default(0)
                ->after('yellow_cards_league_accumulated');
            $table->unsignedTinyInteger('yellow_cards_cup_international_accumulated')
                ->default(0)
                ->after('yellow_cards_cup_national_accumulated');
            $table->unsignedTinyInteger('yellow_cards_friendly_accumulated')
                ->default(0)
                ->after('yellow_cards_cup_international_accumulated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table): void {
            $table->dropColumn([
                'yellow_cards_league_accumulated',
                'yellow_cards_cup_national_accumulated',
                'yellow_cards_cup_international_accumulated',
                'yellow_cards_friendly_accumulated',
            ]);
        });
    }
};

