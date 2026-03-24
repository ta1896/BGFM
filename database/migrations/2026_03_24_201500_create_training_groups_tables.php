<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('color', 24)->default('cyan');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['club_id', 'name']);
        });

        Schema::create('training_group_player', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('training_group_id')->constrained('training_groups')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['training_group_id', 'player_id']);
        });

        Schema::create('training_session_group', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('training_session_id')->constrained('training_sessions')->cascadeOnDelete();
            $table->foreignId('training_group_id')->constrained('training_groups')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['training_session_id', 'training_group_id'], 'tsession_group_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_session_group');
        Schema::dropIfExists('training_group_player');
        Schema::dropIfExists('training_groups');
    }
};
