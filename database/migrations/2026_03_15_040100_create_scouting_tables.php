<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scouting_watchlists', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('priority', 16)->default('medium');
            $table->string('status', 16)->default('watching');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['club_id', 'player_id']);
        });

        Schema::create('scouting_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('watchlist_id')->nullable()->constrained('scouting_watchlists')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('confidence')->default(50);
            $table->unsignedTinyInteger('overall_min');
            $table->unsignedTinyInteger('overall_max');
            $table->unsignedTinyInteger('potential_min');
            $table->unsignedTinyInteger('potential_max');
            $table->unsignedTinyInteger('pace_min');
            $table->unsignedTinyInteger('pace_max');
            $table->unsignedTinyInteger('passing_min');
            $table->unsignedTinyInteger('passing_max');
            $table->unsignedTinyInteger('physical_min');
            $table->unsignedTinyInteger('physical_max');
            $table->string('injury_risk_band', 24);
            $table->string('personality_band', 24);
            $table->text('summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scouting_reports');
        Schema::dropIfExists('scouting_watchlists');
    }
};
