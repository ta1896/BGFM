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
        Schema::create('player_transfer_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->string('season');
            $table->date('transfer_date');
            $table->string('left_club_name');
            $table->string('left_club_tm_id')->nullable();
            $table->foreignId('left_club_id')->nullable()->constrained('clubs')->nullOnDelete();
            $table->string('joined_club_name');
            $table->string('joined_club_tm_id')->nullable();
            $table->foreignId('joined_club_id')->nullable()->constrained('clubs')->nullOnDelete();
            $table->bigInteger('market_value')->nullable();
            $table->string('fee')->nullable();
            $table->boolean('is_loan')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_transfer_histories');
    }
};
