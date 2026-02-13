<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerCareerCompetitionStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
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
}
