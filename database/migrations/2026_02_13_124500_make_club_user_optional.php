<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clubs', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
        });

        DB::statement('ALTER TABLE clubs MODIFY user_id BIGINT UNSIGNED NULL');

        Schema::table('clubs', function (Blueprint $table): void {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
        });

        DB::statement('ALTER TABLE clubs MODIFY user_id BIGINT UNSIGNED NOT NULL');

        Schema::table('clubs', function (Blueprint $table): void {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};

