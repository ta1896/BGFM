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
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->boolean('is_applied')->default(false)->after('notes');
            $table->timestamp('applied_at')->nullable()->after('is_applied');

            $table->index(['club_id', 'is_applied', 'session_date'], 'tsession_club_applied_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->dropIndex('tsession_club_applied_date_idx');
            $table->dropColumn(['is_applied', 'applied_at']);
        });
    }
};
