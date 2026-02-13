<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_processing_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->string('step', 64);
            $table->timestamp('processed_at');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['match_id', 'step'], 'match_processing_steps_match_step_unique');
            $table->index(['step', 'processed_at'], 'match_processing_steps_step_processed_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_processing_steps');
    }
};
