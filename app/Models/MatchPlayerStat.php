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
        'shots_on_target',
        'passes_completed',
        'passes_attempted',
        'passes_failed',
        'long_balls_completed',
        'long_balls_attempted',
        'chances_created',
        'big_chances_created',
        'tackles_won',
        'tackles_lost',
        'saves',
        'xg',
        'xgot',
        'dribbles_completed',
        'dribbles_attempted',
        'duels_won',
        'duels_total',
        'aerials_won',
        'aerials_total',
        'interceptions',
        'recoveries',
        'clearances',
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
