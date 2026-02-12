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
        Schema::create('lineup_player', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lineup_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->string('pitch_position', 20)->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->decimal('x_coord', 5, 2)->nullable();
            $table->decimal('y_coord', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['lineup_id', 'player_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lineup_player');
    }
};
