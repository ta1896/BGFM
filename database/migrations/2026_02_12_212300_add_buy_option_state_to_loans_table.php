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
        Schema::table('loans', function (Blueprint $table) {
            $table->enum('buy_option_state', ['none', 'pending', 'exercised', 'declined'])->default('none')->after('status');
            $table->timestamp('buy_option_decided_at')->nullable()->after('buy_option_state');
            $table->timestamp('bought_at')->nullable()->after('buy_option_decided_at');

            $table->index(['buy_option_state', 'status'], 'loans_option_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropIndex('loans_option_status_idx');
            $table->dropColumn(['buy_option_state', 'buy_option_decided_at', 'bought_at']);
        });
    }
};
