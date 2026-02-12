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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->string('first_name', 80);
            $table->string('last_name', 80);
            $table->enum('position', ['TW', 'LV', 'IV', 'RV', 'LWB', 'RWB', 'LM', 'ZM', 'RM', 'DM', 'OM', 'LAM', 'ZOM', 'RAM', 'LS', 'MS', 'RS', 'LW', 'RW', 'ST']);
            $table->unsignedTinyInteger('age');
            $table->unsignedTinyInteger('overall')->default(50);
            $table->unsignedTinyInteger('pace')->default(50);
            $table->unsignedTinyInteger('shooting')->default(50);
            $table->unsignedTinyInteger('passing')->default(50);
            $table->unsignedTinyInteger('defending')->default(50);
            $table->unsignedTinyInteger('physical')->default(50);
            $table->unsignedTinyInteger('stamina')->default(80);
            $table->unsignedTinyInteger('morale')->default(50);
            $table->decimal('market_value', 12, 2)->default(0);
            $table->decimal('salary', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['club_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
