<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_conversations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('topic', 32);
            $table->string('approach', 32);
            $table->string('outcome', 32)->default('steady');
            $table->smallInteger('happiness_delta')->default(0);
            $table->unsignedTinyInteger('happiness_after')->default(50);
            $table->text('manager_message')->nullable();
            $table->text('player_response')->nullable();
            $table->text('summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_conversations');
    }
};
