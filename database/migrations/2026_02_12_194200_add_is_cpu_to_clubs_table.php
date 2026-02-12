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
        Schema::table('clubs', function (Blueprint $table) {
            $table->boolean('is_cpu')->default(false)->after('user_id');
            $table->index(['is_cpu', 'league_id'], 'clubs_cpu_league_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropIndex('clubs_cpu_league_idx');
            $table->dropColumn('is_cpu');
        });
    }
};
