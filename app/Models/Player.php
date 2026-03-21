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
    
    protected $appends = ['photo_url', 'full_name', 'display_position', 'tm_profile_url', 'sofa_profile_url'];

    protected static function booted()
    {
        static::saving(function ($player) {
            // Auto-calculate attr_market from market_value if market_value changed
            if ($player->isDirty('market_value')) {
                $player->attr_market = min(99, max(1, (int) (pow($player->market_value / 150000000, 0.3) * 100)));
            }

            // Auto-calculate overall from attributes if any attribute or position changed
            if ($player->isDirty(['attr_attacking', 'attr_technical', 'attr_tactical', 'attr_defending', 'attr_creativity', 'attr_market', 'position'])) {
                $player->overall = $player->calculateOverall();
                $player->player_style = $player->calculatePlayerStyle();
            }
        });
    }

    public function calculatePlayerStyle(): string
    {
        $pos = self::mapPosition($this->position);
        $att = $this->attr_attacking ?? 50;
        $tec = $this->attr_technical ?? 50;
        $tac = $this->attr_tactical ?? 50;
        $def = $this->attr_defending ?? 50;
        $cre = $this->attr_creativity ?? 50;

        return match(true) {
            $pos === 'TW' => match(true) {
                ($tec > 65 && $cre > 60) || ($tec > 75) => 'Mitspielender Torwart',
                $def > 75 => 'Linien-Torwart',
                default => 'Torwart-Spezialist'
            },
            $pos === 'IV' => match(true) {
                $def > 78 && $tac > 72 => 'Zweikampfmonster', // "Zweikampfstarker IV"
                $tec > 68 && $tac > 68 => 'Spielstarker IV',
                default => 'Defensiv-Anker'
            },
            in_array($pos, ['LV', 'RV']) => match(true) {
                $att > 68 && $tec > 65 => 'Offensiv-Flitzer',
                $def > 75 && $tac > 70 => 'Defensiv-Spezialist',
                default => 'Zweikampfstarker AV'
            },
            in_array($pos, ['DM', 'ZM']) => match(true) {
                $def > 68 && $att > 62 => 'Box-to-Box',
                $cre > 72 && $tec > 72 => 'Regisseur',
                $def > 78 => 'Abräumer',
                default => 'Strategischer DM'
            },
            in_array($pos, ['OM', 'LW', 'RW']) => match(true) {
                $tec > 78 && $cre > 72 => 'Dribbelkünstler',
                $cre > 78 => 'Spielgestalter',
                default => 'Flügel-Flitzer'
            },
            $pos === 'ST' => match(true) {
                $att > 78 && $tec > 70 => 'Knipser',
                $att > 72 && $def > 55 => 'Zielspieler',
                default => 'Dynamische Spitze'
            },
            default => 'Allrounder'
        };
    }

    public function calculateOverall(): int
    {
        $pos = self::mapPosition($this->position);
        
        $attacking = $this->attr_attacking ?? 50;
        $technical = $this->attr_technical ?? 50;
        $tactical = $this->attr_tactical ?? 50;
        $defending = $this->attr_defending ?? 50;
        $creativity = $this->attr_creativity ?? 50;
        $market = $this->attr_market ?? 50;

        return (int) match(true) {
            $pos === 'TW' => (
                ($technical * 2.0 + $tactical * 1.5 + $defending * 2.0 + $market) / 6.5
            ),
            $pos === 'IV' => (
                ($defending * 3.0 + $tactical * 1.5 + $market) / 5.5
            ),
            in_array($pos, ['LV', 'RV']) => (
                ($defending * 2.0 + $tactical * 1.5 + $technical * 1.0 + $attacking * 0.5 + $market) / 6
            ),
            in_array($pos, ['DM', 'ZM']) => (
                ($technical * 1.5 + $creativity * 1.5 + $tactical * 1.2 + $defending * 0.8 + $market) / 6
            ),
            in_array($pos, ['OM', 'LM', 'RM']) => (
                ($creativity * 2.0 + $technical * 1.5 + $tactical * 1.0 + $attacking * 1.0 + $market) / 6.5
            ),
            in_array($pos, ['LF', 'RF', 'HS']) => (
                ($attacking * 2.0 + $technical * 1.5 + $creativity * 1.5 + $tactical * 1.0 + $market) / 7
            ),
            $pos === 'MS' => (
                ($attacking * 3.0 + $technical * 1.5 + $creativity * 0.5 + $market) / 6
            ),
            default => ( // Fallback Allrounder
                ($attacking + $technical + $tactical + $defending + $creativity + $market) / 6
            )
        };
    }

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
        'transfermarkt_id',
        'transfermarkt_url',
        'sofascore_id',
        'player_style',
        'attr_attacking',
        'attr_technical',
        'attr_tactical',
        'attr_defending',
        'attr_creativity',
        'attr_market',
        'is_imported',
    ];

    protected function casts(): array
    {
        return [
            'market_value' => 'integer',
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
            'is_imported' => 'boolean',
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

    public function getTmProfileUrlAttribute(): ?string
    {
        return $this->transfermarkt_url;
    }

    public function getSofaProfileUrlAttribute(): ?string
    {
        if (!$this->sofascore_id) {
            return null;
        }
        return "https://www.sofascore.com/player/{$this->sofascore_id}";
    }
}
