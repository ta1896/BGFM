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
        Schema::create('friendly_match_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('challenger_club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('challenged_club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('accepted_match_id')->nullable()->constrained('matches')->nullOnDelete();
            $table->dateTime('kickoff_at');
            $table->foreignId('stadium_club_id')->nullable()->constrained('clubs')->nullOnDelete();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'cancelled', 'auto_accepted'])->default('pending');
            $table->string('message', 255)->nullable();
            $table->dateTime('responded_at')->nullable();
            $table->timestamps();

            $table->index(['challenger_club_id', 'status', 'kickoff_at'], 'frequest_challenger_status_kickoff_idx');
            $table->index(['challenged_club_id', 'status', 'kickoff_at'], 'frequest_challenged_status_kickoff_idx');
            $table->index(['status', 'kickoff_at'], 'frequest_status_kickoff_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('friendly_match_requests');
    }
};
