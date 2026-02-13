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
        Schema::create('cup_reward_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('competition_season_id')->constrained('competition_seasons')->cascadeOnDelete();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->string('event_key', 120);
            $table->string('stage', 80);
            $table->unsignedSmallInteger('source_round_number');
            $table->unsignedSmallInteger('target_round_number')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->dateTime('rewarded_at');
            $table->timestamps();

            $table->unique(
                ['competition_season_id', 'club_id', 'event_key'],
                'cup_reward_logs_unique_event'
            );
            $table->index(['competition_season_id', 'source_round_number'], 'cup_reward_logs_round_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cup_reward_logs');
    }
};

