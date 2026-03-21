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
            $table->boolean('is_imported')->default(false)->after('transfermarkt_url');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->boolean('is_imported')->default(false)->after('transfermarkt_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn('is_imported');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('is_imported');
        });
    }
};
