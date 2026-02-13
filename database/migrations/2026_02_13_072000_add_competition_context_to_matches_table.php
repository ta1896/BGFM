<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table): void {
            $table->enum('competition_context', ['league', 'cup_national', 'cup_international', 'friendly'])
                ->nullable()
                ->after('type');
            $table->index(['competition_context', 'status', 'kickoff_at'], 'matches_context_status_kickoff_idx');
        });

        $rows = DB::table('matches as m')
            ->leftJoin('competition_seasons as cs', 'cs.id', '=', 'm.competition_season_id')
            ->leftJoin('competitions as c', 'c.id', '=', 'cs.competition_id')
            ->select([
                'm.id',
                'm.type',
                'c.country_id as competition_country_id',
            ])
            ->get();

        foreach ($rows as $row) {
            $type = strtolower((string) $row->type);
            $context = match ($type) {
                'league' => 'league',
                'friendly' => 'friendly',
                'cup' => $row->competition_country_id ? 'cup_national' : 'cup_international',
                default => 'friendly',
            };

            DB::table('matches')
                ->where('id', (int) $row->id)
                ->update(['competition_context' => $context]);
        }
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table): void {
            $table->dropIndex('matches_context_status_kickoff_idx');
            $table->dropColumn('competition_context');
        });
    }
};
