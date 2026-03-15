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
    
    protected $appends = ['photo_url', 'full_name', 'display_position'];

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
        'squad_role',
        'leadership_level',
        'team_status',
        'expected_playtime',
        'role_override_active',
        'role_override_set_at',
        'happiness',
        'happiness_trend',
        'fatigue',
        'sharpness',
        'injury_proneness',
        'match_load',
        'training_load',
        'medical_status',
        'last_morale_reason',
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
            'expected_playtime' => 'integer',
            'role_override_active' => 'boolean',
            'role_override_set_at' => 'datetime',
            'happiness' => 'integer',
            'happiness_trend' => 'integer',
            'fatigue' => 'integer',
            'sharpness' => 'integer',
            'injury_proneness' => 'integer',
            'match_load' => 'integer',
            'training_load' => 'integer',
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

    public function playtimePromises(): HasMany
    {
        return $this->hasMany(PlayerPlaytimePromise::class);
    }

    public function injuries(): HasMany
    {
        return $this->hasMany(PlayerInjury::class);
    }

    public function recoveryLogs(): HasMany
    {
        return $this->hasMany(PlayerRecoveryLog::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(PlayerConversation::class);
    }

    public function scoutingReports(): HasMany
    {
        return $this->hasMany(ScoutingReport::class);
    }

    public function scoutingWatchlists(): HasMany
    {
        return $this->hasMany(ScoutingWatchlist::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
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

    public static function mapPosition(?string $position): ?string
    {
        if (!$position) {
            return null;
        }

        $map = [
            'GK' => 'TW',
            'LB' => 'LV',
            'CB' => 'IV',
            'RB' => 'RV',
            'LWB' => 'LV',
            'RWB' => 'RV',
            'CDM' => 'DM',
            'CM' => 'ZM',
            'CAM' => 'OM',
            'LM' => 'LM',
            'RM' => 'RM',
            'LW' => 'LF',
            'RW' => 'RF',
            'ST' => 'MS',
            'CF' => 'HS',
            'LS' => 'MS',
            'RS' => 'MS',
        ];

        return $map[$position] ?? $position;
    }

    public function getDisplayPositionAttribute(): string
    {
        return self::mapPosition($this->position) ?? (string) $this->position;
    }
}
