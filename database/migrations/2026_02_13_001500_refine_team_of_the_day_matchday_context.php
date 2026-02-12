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
        Schema::table('team_of_the_days', function (Blueprint $table) {
            $table->dropUnique('team_of_the_days_for_date_unique');

            $table->foreignId('competition_season_id')
                ->nullable()
                ->after('for_date')
                ->constrained('competition_seasons')
                ->nullOnDelete();
            $table->unsignedSmallInteger('matchday')
                ->nullable()
                ->after('competition_season_id');

            $table->index('for_date', 'totd_for_date_idx');
            $table->unique(
                ['competition_season_id', 'matchday', 'generation_context'],
                'totd_context_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_of_the_days', function (Blueprint $table) {
            $table->dropUnique('totd_context_unique');
            $table->dropIndex('totd_for_date_idx');
            $table->dropConstrainedForeignId('competition_season_id');
            $table->dropColumn('matchday');

            $table->unique('for_date');
        });
    }
};
