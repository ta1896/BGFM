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
            $table->dropColumn([
                'pace',
                'shooting',
                'passing',
                'defending',
                'physical',
                'stamina',
                'morale'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->integer('pace')->default(50);
            $table->integer('shooting')->default(50);
            $table->integer('passing')->default(50);
            $table->integer('defending')->default(50);
            $table->integer('physical')->default(50);
            $table->integer('stamina')->default(50);
            $table->integer('morale')->default(50);
        });
    }
};
