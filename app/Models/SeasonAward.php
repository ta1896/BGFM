<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeasonAward extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_season_id',
        'award_key',
        'label',
        'player_id',
        'club_id',
        'user_id',
        'value_numeric',
        'value_label',
        'summary',
    ];

    protected function casts(): array
    {
        return [
            'value_numeric' => 'decimal:2',
        ];
    }

    public function competitionSeason(): BelongsTo
    {
        return $this->belongsTo(CompetitionSeason::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
