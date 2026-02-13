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
        Schema::table('matches', function (Blueprint $table): void {
            $table->string('live_processing_token', 64)
                ->nullable()
                ->after('live_error_message');
            $table->dateTime('live_processing_started_at')
                ->nullable()
                ->after('live_processing_token');
            $table->dateTime('live_processing_last_run_at')
                ->nullable()
                ->after('live_processing_started_at');
            $table->unsignedInteger('live_processing_attempts')
                ->default(0)
                ->after('live_processing_last_run_at');
            $table->string('live_processing_last_error', 255)
                ->nullable()
                ->after('live_processing_attempts');

            $table->index(['live_processing_started_at', 'status'], 'matches_live_processing_started_status_idx');
            $table->index(['live_processing_token'], 'matches_live_processing_token_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table): void {
            $table->dropIndex('matches_live_processing_started_status_idx');
            $table->dropIndex('matches_live_processing_token_idx');
            $table->dropColumn([
                'live_processing_token',
                'live_processing_started_at',
                'live_processing_last_run_at',
                'live_processing_attempts',
                'live_processing_last_error',
            ]);
        });
    }
};

