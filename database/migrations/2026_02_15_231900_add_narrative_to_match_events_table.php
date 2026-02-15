<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add a narrative text column to match_events so the quick simulation
     * can store generated ticker template texts.
     */
    public function up(): void
    {
        Schema::table('match_events', function (Blueprint $table) {
            $table->text('narrative')->nullable()->after('metadata');
        });
    }

    public function down(): void
    {
        Schema::table('match_events', function (Blueprint $table) {
            $table->dropColumn('narrative');
        });
    }
};
