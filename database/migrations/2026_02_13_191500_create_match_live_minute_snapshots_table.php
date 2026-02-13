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
        Schema::create('match_live_minute_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->unsignedTinyInteger('minute');
            $table->unsignedTinyInteger('home_score')->default(0);
            $table->unsignedTinyInteger('away_score')->default(0);
            $table->string('home_phase', 30)->nullable();
            $table->string('away_phase', 30)->nullable();
            $table->string('home_tactical_style', 20)->nullable();
            $table->string('away_tactical_style', 20)->nullable();
            $table->unsignedTinyInteger('pending_plans')->default(0);
            $table->unsignedTinyInteger('executed_plans')->default(0);
            $table->unsignedTinyInteger('skipped_plans')->default(0);
            $table->unsignedTinyInteger('invalid_plans')->default(0);
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['match_id', 'minute'], 'mliveminute_match_minute_unique');
            $table->index(['match_id', 'updated_at'], 'mliveminute_match_updated_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_live_minute_snapshots');
    }
};

