<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('season_awards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('competition_season_id')->constrained()->cascadeOnDelete();
            $table->string('award_key', 64);
            $table->string('label', 120);
            $table->foreignId('player_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('club_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('value_numeric', 10, 2)->nullable();
            $table->string('value_label', 80)->nullable();
            $table->text('summary')->nullable();
            $table->timestamps();
            $table->unique(['competition_season_id', 'award_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('season_awards');
    }
};
