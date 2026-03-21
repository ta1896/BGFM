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
            $table->string('sofascore_id')->nullable()->after('transfermarkt_url');
            $table->unsignedTinyInteger('attr_attacking')->default(50)->after('sofascore_id');
            $table->unsignedTinyInteger('attr_technical')->default(50)->after('attr_attacking');
            $table->unsignedTinyInteger('attr_tactical')->default(50)->after('attr_technical');
            $table->unsignedTinyInteger('attr_defending')->default(50)->after('attr_tactical');
            $table->unsignedTinyInteger('attr_creativity')->default(50)->after('attr_defending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn([
                'sofascore_id',
                'attr_attacking',
                'attr_technical',
                'attr_tactical',
                'attr_defending',
                'attr_creativity',
            ]);
        });
    }
};
