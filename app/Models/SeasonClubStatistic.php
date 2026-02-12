<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeasonClubStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_season_id',
        'club_id',
        'matches_played',
        'wins',
        'draws',
        'losses',
        'goals_for',
        'goals_against',
        'goal_diff',
        'points',
        'home_points',
        'away_points',
        'form_last5',
    ];

    public function competitionSeason(): BelongsTo
    {
        return $this->belongsTo(CompetitionSeason::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
