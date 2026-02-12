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
            $table->foreignId('parent_club_id')->nullable()->after('club_id')->constrained('clubs')->nullOnDelete();
            $table->date('loan_ends_on')->nullable()->after('contract_expires_on');

            $table->index(['parent_club_id', 'loan_ends_on'], 'players_parent_loanend_idx');
        });

        Schema::create('loan_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('lender_club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('listed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('min_weekly_fee', 12, 2)->default(0);
            $table->decimal('buy_option_price', 12, 2)->nullable();
            $table->unsignedTinyInteger('loan_months')->default(6);
            $table->dateTime('listed_at');
            $table->dateTime('expires_at');
            $table->enum('status', ['open', 'loaned', 'completed', 'cancelled'])->default('open');
            $table->timestamps();

            $table->index(['status', 'expires_at'], 'lloan_status_expires_idx');
        });

        Schema::create('loan_bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_listing_id')->constrained('loan_listings')->cascadeOnDelete();
            $table->foreignId('borrower_club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('bidder_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('weekly_fee', 12, 2);
            $table->string('message', 255)->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'withdrawn'])->default('pending');
            $table->dateTime('decided_at')->nullable();
            $table->timestamps();

            $table->index(['loan_listing_id', 'status'], 'lbid_listing_status_idx');
            $table->index(['borrower_club_id', 'status'], 'lbid_borrower_status_idx');
        });

        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_listing_id')->nullable()->constrained('loan_listings')->nullOnDelete();
            $table->foreignId('loan_bid_id')->nullable()->constrained('loan_bids')->nullOnDelete();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('lender_club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('borrower_club_id')->constrained('clubs')->cascadeOnDelete();
            $table->decimal('weekly_fee', 12, 2)->default(0);
            $table->decimal('buy_option_price', 12, 2)->nullable();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->enum('status', ['active', 'completed', 'terminated'])->default('active');
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'ends_on'], 'loans_status_ends_idx');
            $table->index(['player_id', 'status'], 'loans_player_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
        Schema::dropIfExists('loan_bids');
        Schema::dropIfExists('loan_listings');

        Schema::table('players', function (Blueprint $table) {
            $table->dropIndex('players_parent_loanend_idx');
            $table->dropConstrainedForeignId('parent_club_id');
            $table->dropColumn('loan_ends_on');
        });
    }
};
