<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoutingReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'player_id',
        'watchlist_id',
        'created_by_user_id',
        'confidence',
        'overall_min',
        'overall_max',
        'potential_min',
        'potential_max',
        'pace_min',
        'pace_max',
        'passing_min',
        'passing_max',
        'physical_min',
        'physical_max',
        'injury_risk_band',
        'personality_band',
        'summary',
        'created_at',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function watchlist(): BelongsTo
    {
        return $this->belongsTo(ScoutingWatchlist::class, 'watchlist_id');
    }
}
