<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GameMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'competition_season_id',
        'season_id',
        'type',
        'stage',
        'round_number',
        'matchday',
        'kickoff_at',
        'status',
        'home_club_id',
        'away_club_id',
        'stadium_club_id',
        'home_score',
        'away_score',
        'extra_time',
        'penalties_home',
        'penalties_away',
        'attendance',
        'weather',
        'simulation_seed',
        'played_at',
    ];

    protected function casts(): array
    {
        return [
            'kickoff_at' => 'datetime',
            'played_at' => 'datetime',
            'extra_time' => 'boolean',
        ];
    }

    public function competitionSeason(): BelongsTo
    {
        return $this->belongsTo(CompetitionSeason::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function homeClub(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'home_club_id');
    }

    public function awayClub(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'away_club_id');
    }

    public function stadiumClub(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'stadium_club_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(MatchEvent::class, 'match_id')
            ->orderBy('minute')
            ->orderBy('second');
    }

    public function playerStats(): HasMany
    {
        return $this->hasMany(MatchPlayerStat::class, 'match_id');
    }

    public function financialSettlement(): HasOne
    {
        return $this->hasOne(MatchFinancialSettlement::class, 'match_id');
    }

    public function friendlyRequest(): HasOne
    {
        return $this->hasOne(FriendlyMatchRequest::class, 'accepted_match_id');
    }
}
