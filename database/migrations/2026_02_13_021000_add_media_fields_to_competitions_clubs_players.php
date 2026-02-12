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
        Schema::table('competitions', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('short_name');
        });

        Schema::table('clubs', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('short_name');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('last_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->dropColumn('logo_path');
        });

        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn('logo_path');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });
    }
};
