<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('players', 'player_style')) {
            Schema::table('players', function (Blueprint $table) {
                $table->string('player_style')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('player_style');
        });
    }
};
