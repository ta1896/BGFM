<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchPlayerStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'club_id',
        'player_id',
        'lineup_role',
        'position_code',
        'rating',
        'minutes_played',
        'goals',
        'assists',
        'yellow_cards',
        'red_cards',
        'shots',
        'passes_completed',
        'passes_failed',
        'tackles_won',
        'tackles_lost',
        'saves',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
