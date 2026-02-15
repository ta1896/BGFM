<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('match_ticker_templates', function (Blueprint $table) {
            $table->id();
            $table->string('event_type')->index(); // e.g., goal, yellow_card, red_card, chance, foul, substitution
            $table->text('text');
            $table->string('priority')->default('normal'); // For future use (e.g., high priority for big moments)
            $table->string('locale')->default('de');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_ticker_templates');
    }
};
