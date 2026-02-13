<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerSeasonCompetitionStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'season_id',
        'competition_context',
        'appearances',
        'minutes_played',
        'goals',
        'assists',
        'yellow_cards',
        'red_cards',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }
}
