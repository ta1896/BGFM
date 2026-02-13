<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table): void {
            $table->string('position_main', 8)->nullable()->after('position');
            $table->unsignedTinyInteger('suspension_league_remaining')->default(0)->after('suspension_matches_remaining');
            $table->unsignedTinyInteger('suspension_cup_national_remaining')->default(0)->after('suspension_league_remaining');
            $table->unsignedTinyInteger('suspension_cup_international_remaining')->default(0)->after('suspension_cup_national_remaining');
            $table->unsignedTinyInteger('suspension_friendly_remaining')->default(0)->after('suspension_cup_international_remaining');

            $table->index(
                ['club_id', 'suspension_league_remaining', 'suspension_cup_national_remaining', 'suspension_cup_international_remaining', 'suspension_friendly_remaining'],
                'players_club_suspension_context_idx'
            );
        });

        DB::table('players')->update([
            'position_main' => DB::raw('position'),
            'suspension_league_remaining' => DB::raw('suspension_matches_remaining'),
        ]);
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table): void {
            $table->dropIndex('players_club_suspension_context_idx');
            $table->dropColumn([
                'position_main',
                'suspension_league_remaining',
                'suspension_cup_national_remaining',
                'suspension_cup_international_remaining',
                'suspension_friendly_remaining',
            ]);
        });
    }
};
