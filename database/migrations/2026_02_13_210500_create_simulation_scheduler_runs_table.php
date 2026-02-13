<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulation_scheduler_runs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('run_token')->nullable()->index();
            $table->string('status', 32)->index();
            $table->string('trigger', 32)->default('scheduled');
            $table->boolean('forced')->default(false);
            $table->unsignedInteger('requested_limit')->default(0);
            $table->unsignedTinyInteger('requested_minutes_per_run')->default(5);
            $table->json('requested_types')->nullable();
            $table->unsignedInteger('runner_lock_seconds')->default(120);
            $table->unsignedInteger('candidate_matches')->default(0);
            $table->unsignedInteger('claimed_matches')->default(0);
            $table->unsignedInteger('processed_matches')->default(0);
            $table->unsignedInteger('failed_matches')->default(0);
            $table->unsignedInteger('skipped_active_claims')->default(0);
            $table->unsignedInteger('skipped_unclaimable')->default(0);
            $table->unsignedInteger('stale_claim_takeovers')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->string('message', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulation_scheduler_runs');
    }
};

