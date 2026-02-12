<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchLivePlayerState extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'club_id',
        'player_id',
        'slot',
        'is_on_pitch',
        'is_sent_off',
        'is_injured',
        'fit_factor',
        'minutes_played',
        'ball_contacts',
        'pass_attempts',
        'pass_completions',
        'tackle_attempts',
        'tackle_won',
        'fouls_committed',
        'fouls_suffered',
        'shots',
        'shots_on_target',
        'goals',
        'assists',
        'yellow_cards',
        'red_cards',
        'saves',
    ];

    protected function casts(): array
    {
        return [
            'is_on_pitch' => 'boolean',
            'is_sent_off' => 'boolean',
            'is_injured' => 'boolean',
            'fit_factor' => 'decimal:2',
        ];
    }

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
