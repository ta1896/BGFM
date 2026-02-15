<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('match_live_actions', function (Blueprint $table) {
            // Visualization coordinates (0-100 scale)
            $table->integer('x_coord')->nullable()->after('metadata');
            $table->integer('y_coord')->nullable()->after('x_coord');

            // Expected Goals (0.00 - 1.00)
            $table->decimal('xg', 5, 4)->nullable()->after('y_coord');

            // Momentum value at this moment (-100 to 100)
            // Positive = Home dominance, Negative = Away dominance
            $table->integer('momentum_value')->nullable()->after('xg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('match_live_actions', function (Blueprint $table) {
            $table->dropColumn(['x_coord', 'y_coord', 'xg', 'momentum_value']);
        });
    }
};
