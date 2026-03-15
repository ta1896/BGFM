<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scouting_discoveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('market', 24)->default('domestic');
            $table->string('position_group', 24)->default('all');
            $table->string('age_band', 24)->default('all');
            $table->string('value_band', 24)->default('all');
            $table->string('discovery_level', 24)->default('experienced');
            $table->unsignedTinyInteger('fit_score')->default(0);
            $table->string('market_band', 64)->nullable();
            $table->string('region_tag', 64)->nullable();
            $table->string('discovery_note', 160)->nullable();
            $table->timestamp('scanned_at')->nullable();
            $table->timestamps();

            $table->unique(['club_id', 'player_id'], 'scouting_discoveries_club_player_unique');
            $table->index(['club_id', 'market', 'discovery_level'], 'scouting_discoveries_market_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scouting_discoveries');
    }
};
