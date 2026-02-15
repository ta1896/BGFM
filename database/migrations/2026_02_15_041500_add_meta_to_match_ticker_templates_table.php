<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('match_ticker_templates', function (Blueprint $table) {
            $table->string('mood')->default('neutral')->after('priority');
            $table->string('commentator_style')->default('sachlich')->after('mood');

            // Adding an index for faster filtering during simulation
            $table->index(['event_type', 'mood', 'commentator_style'], 'idx_ticker_lookup');
        });
    }

    public function down(): void
    {
        Schema::table('match_ticker_templates', function (Blueprint $table) {
            $table->dropIndex('idx_ticker_lookup');
            $table->dropColumn(['mood', 'commentator_style']);
        });
    }
};
