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
        Schema::table('lineups', function (Blueprint $table) {
            $table->string('pressing_intensity')->default('normal')->after('aggression');
            $table->string('line_of_engagement')->default('normal')->after('line_height');
            $table->string('pressing_trap')->default('none')->after('time_wasting');
            $table->string('cross_engagement')->default('none')->after('pressing_trap');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lineups', function (Blueprint $table) {
            $table->dropColumn(['pressing_intensity', 'line_of_engagement', 'pressing_trap', 'cross_engagement']);
        });
    }
};
