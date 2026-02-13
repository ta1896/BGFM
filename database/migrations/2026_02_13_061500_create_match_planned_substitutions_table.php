<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_planned_substitutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('player_out_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('player_in_id')->nullable()->constrained('players')->nullOnDelete();
            $table->unsignedTinyInteger('planned_minute');
            $table->enum('score_condition', ['any', 'leading', 'drawing', 'trailing'])->default('any');
            $table->string('target_slot', 20)->nullable();
            $table->enum('status', ['pending', 'executed', 'skipped', 'invalid'])->default('pending');
            $table->unsignedTinyInteger('executed_minute')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['match_id', 'club_id', 'status', 'planned_minute'], 'mplans_match_club_status_min_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_planned_substitutions');
    }
};
