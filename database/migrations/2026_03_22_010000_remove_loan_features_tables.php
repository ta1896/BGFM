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
        // Drop loan tables
        Schema::dropIfExists('loans');
        Schema::dropIfExists('loan_bids');
        Schema::dropIfExists('loan_listings');

        // Remove loan columns from players table
        Schema::table('players', function (Blueprint $table) {
            if (Schema::hasColumn('players', 'parent_club_id')) {
                $table->dropForeign(['parent_club_id']);
                $table->dropColumn('parent_club_id');
            }
            if (Schema::hasColumn('players', 'loan_ends_on')) {
                $table->dropColumn('loan_ends_on');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback for this cleanup migration as it deletes core legacy data structure.
        // If needed, the previous migrations (e.g., 2026_02_12_202100_add_loans_and_contract_extensions.php) 
        // would need to be re-run manually.
    }
};
