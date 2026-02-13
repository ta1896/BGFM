<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimulationSchedulerRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'run_token',
        'status',
        'trigger',
        'forced',
        'requested_limit',
        'requested_minutes_per_run',
        'requested_types',
        'runner_lock_seconds',
        'candidate_matches',
        'claimed_matches',
        'processed_matches',
        'failed_matches',
        'skipped_active_claims',
        'skipped_unclaimable',
        'stale_claim_takeovers',
        'started_at',
        'finished_at',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'forced' => 'boolean',
            'requested_limit' => 'integer',
            'requested_minutes_per_run' => 'integer',
            'runner_lock_seconds' => 'integer',
            'requested_types' => 'array',
            'candidate_matches' => 'integer',
            'claimed_matches' => 'integer',
            'processed_matches' => 'integer',
            'failed_matches' => 'integer',
            'skipped_active_claims' => 'integer',
            'skipped_unclaimable' => 'integer',
            'stale_claim_takeovers' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}

