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
        Schema::table('players', function (Blueprint $table) {
            if (!Schema::hasColumn('players', 'height')) {
                $table->integer('height')->nullable()->after('birthday');
            }
            if (!Schema::hasColumn('players', 'shirt_number')) {
                $table->integer('shirt_number')->nullable()->after('height');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn(['height', 'shirt_number']);
        });
    }
};
