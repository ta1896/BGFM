<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('match_live_team_states')) {
            return;
        }

        if (!Schema::hasColumn('match_live_team_states', 'tactical_style')) {
            Schema::table('match_live_team_states', function (Blueprint $table): void {
                $table->string('tactical_style', 20)
                    ->default('balanced')
                    ->after('last_substitution_minute');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('match_live_team_states')) {
            return;
        }

        if (Schema::hasColumn('match_live_team_states', 'tactical_style')) {
            Schema::table('match_live_team_states', function (Blueprint $table): void {
                $table->dropColumn('tactical_style');
            });
        }
    }
};
