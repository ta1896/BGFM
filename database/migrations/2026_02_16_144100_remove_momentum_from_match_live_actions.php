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
            $table->dropColumn('momentum_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('match_live_actions', function (Blueprint $table) {
            $table->integer('momentum_value')->nullable()->after('xg');
        });
    }
};
