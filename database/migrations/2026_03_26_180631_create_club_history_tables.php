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
        Schema::create('club_hall_of_fame', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->foreignId('club_id')->constrained()->onDelete('cascade');
            $table->date('inducted_at')->nullable();
            $table->string('legend_type')->default('icon');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('club_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->onDelete('cascade');
            $table->string('record_key'); // e.g. 'all_time_goals'
            $table->string('record_value');
            $table->string('reference_type')->nullable(); // e.g. App\Models\Player
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->date('achieved_at')->nullable();
            $table->timestamps();

            $table->index(['club_id', 'record_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_hall_of_fame');
        Schema::dropIfExists('club_records');
    }
};
