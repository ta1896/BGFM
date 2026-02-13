<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CupRewardLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_season_id',
        'club_id',
        'event_key',
        'stage',
        'source_round_number',
        'target_round_number',
        'amount',
        'rewarded_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'rewarded_at' => 'datetime',
        ];
    }

    public function competitionSeason(): BelongsTo
    {
        return $this->belongsTo(CompetitionSeason::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}

