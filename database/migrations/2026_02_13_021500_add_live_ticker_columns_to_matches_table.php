<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->unsignedTinyInteger('live_minute')->default(0)->after('status');
            $table->boolean('live_paused')->default(false)->after('live_minute');
            $table->string('live_error_message', 255)->nullable()->after('live_paused');
            $table->dateTime('live_last_tick_at')->nullable()->after('live_error_message');
        });
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn(['live_minute', 'live_paused', 'live_error_message', 'live_last_tick_at']);
        });
    }
};
