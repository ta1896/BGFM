<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'parent_club_id',
        'first_name',
        'last_name',
        'photo_path',
        'position',
        'position_main',
        'position_second',
        'position_third',
        'preferred_foot',
        'age',
        'overall',
        'potential',
        'pace',
        'shooting',
        'passing',
        'defending',
        'physical',
        'stamina',
        'morale',
        'status',
        'market_value',
        'salary',
        'contract_expires_on',
        'loan_ends_on',
        'last_training_at',
        'injury_matches_remaining',
        'suspension_matches_remaining',
        'suspension_league_remaining',
        'suspension_cup_national_remaining',
        'suspension_cup_international_remaining',
        'suspension_friendly_remaining',
        'yellow_cards_league_accumulated',
        'yellow_cards_cup_national_accumulated',
        'yellow_cards_cup_international_accumulated',
        'yellow_cards_friendly_accumulated',
    ];

    protected function casts(): array
    {
        return [
            'market_value' => 'decimal:2',
            'salary' => 'decimal:2',
            'contract_expires_on' => 'date',
            'loan_ends_on' => 'date',
            'last_training_at' => 'datetime',
            'injury_matches_remaining' => 'integer',
            'suspension_matches_remaining' => 'integer',
            'suspension_league_remaining' => 'integer',
            'suspension_cup_national_remaining' => 'integer',
            'suspension_cup_international_remaining' => 'integer',
            'suspension_friendly_remaining' => 'integer',
            'yellow_cards_league_accumulated' => 'integer',
            'yellow_cards_cup_national_accumulated' => 'integer',
            'yellow_cards_cup_international_accumulated' => 'integer',
            'yellow_cards_friendly_accumulated' => 'integer',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function parentClub(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'parent_club_id');
    }

    public function lineups(): BelongsToMany
    {
        return $this->belongsToMany(Lineup::class)
            ->withPivot([
                'pitch_position',
                'sort_order',
                'x_coord',
                'y_coord',
                'is_captain',
                'is_set_piece_taker',
                'is_bench',
                'bench_order',
            ])
            ->withTimestamps();
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(PlayerContract::class);
    }

    public function loanListings(): HasMany
    {
        return $this->hasMany(LoanListing::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function nationalTeamCallups(): HasMany
    {
        return $this->hasMany(NationalTeamCallup::class);
    }

    public function teamOfTheDayEntries(): HasMany
    {
        return $this->hasMany(TeamOfTheDayPlayer::class);
    }

    public function randomEventOccurrences(): HasMany
    {
        return $this->hasMany(RandomEventOccurrence::class);
    }

    public function liveStates(): HasMany
    {
        return $this->hasMany(MatchLivePlayerState::class);
    }

    public function seasonCompetitionStatistics(): HasMany
    {
        return $this->hasMany(PlayerSeasonCompetitionStatistic::class);
    }

    public function careerCompetitionStatistics(): HasMany
    {
        return $this->hasMany(PlayerCareerCompetitionStatistic::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function getPhotoUrlAttribute(): string
    {
        if (!$this->photo_path) {
            return asset('images/placeholders/player.svg');
        }

        if (str_starts_with($this->photo_path, 'http://') || str_starts_with($this->photo_path, 'https://')) {
            return $this->photo_path;
        }

        return Storage::url($this->photo_path);
    }
}
