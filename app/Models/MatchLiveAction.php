<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchLiveAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'minute',
        'second',
        'sequence',
        'club_id',
        'player_id',
        'opponent_player_id',
        'action_type',
        'outcome',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
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

    public function opponentPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'opponent_player_id');
    }
}
