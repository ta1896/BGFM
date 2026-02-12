<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamOfTheDayPlayer extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_of_the_day_id',
        'player_id',
        'club_id',
        'position_code',
        'rating',
        'stats_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
            'stats_snapshot' => 'array',
        ];
    }

    public function teamOfTheDay(): BelongsTo
    {
        return $this->belongsTo(TeamOfTheDay::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
