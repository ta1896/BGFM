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
    
    protected $appends = ['photo_url', 'full_name', 'display_position', 'tm_profile_url', 'sofa_profile_url', 'nationality_code', 'position_long'];

    protected static function booted()
    {
        static::creating(function ($player) {
            if (!isset($player->happiness)) $player->happiness = 100;
            if (!isset($player->sharpness)) $player->sharpness = 100;
            if (!isset($player->fatigue)) $player->fatigue = 0;
        });

        static::saving(function ($player) {
            // Auto-calculate attr_market from market_value if market_value changed
            if ($player->isDirty('market_value')) {
                $player->attr_market = min(99, max(1, (int) (pow($player->market_value / 150000000, 0.3) * 100)));
            }

            // Auto-calculate age from birthday if birthday changed
            if ($player->isDirty('birthday') && $player->birthday) {
                $player->age = $player->birthday->age;
            }

            // Auto-calculate overall from attributes if any attribute or position changed
            if ($player->isDirty(['attr_attacking', 'technical', 'attr_tactical', 'attr_defending', 'attr_creativity', 'attr_market', 'position'])) {
                $player->overall = $player->calculateOverall();
                $player->player_style = $player->calculatePlayerStyle();
                $player->potential = $player->calculatePotential();
            }

            if ($player->isDirty(['age', 'overall']) && !$player->isDirty('potential')) {
                $player->potential = $player->calculatePotential();
            }
        });
    }

    public function calculatePlayerStyle(): string
    {
        $pos = self::mapPosition($this->position);
        $att = $this->attr_attacking ?? 50;
        $tec = $this->technical ?? 50;
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
            in_array($pos, ['OM', 'LM', 'RM']) => match(true) {
                $tec > 78 && $cre > 72 => 'Dribbelkünstler',
                $cre > 78 => 'Spielgestalter',
                default => 'Offensiv-Allrounder'
            },
            in_array($pos, ['LF', 'RF']) => match(true) {
                $tec > 78 && $att > 75 => 'Inside Forward',
                $cre > 78 && $tec > 75 => 'Klassischer Flügel',
                $att > 78 => 'Außenstürmer',
                default => 'Flügelstürmer'
            },
            in_array($pos, ['MS', 'HS', 'LF', 'RF']) => match(true) {
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
        $technical = $this->technical ?? 50;
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
            in_array($pos, ['MS']) => (
                ($attacking * 3.0 + $technical * 1.5 + $creativity * 0.5 + $market) / 6
            ),
            default => (
                ($attacking * 1.0 + $technical * 1.0 + $tactical * 1.0 + $defending * 1.0 + $creativity * 1.0 + $market) / 6
            ),
        };
    }

    public function calculatePotential(): int
    {
        $age = $this->age ?? 25;
        $overall = $this->overall ?? 50;
        
        // Potential logic: Young players grow more. 
        // Peak growth usually around 26-27.
        $growthRoom = max(0, 27 - $age);
        
        // Multiplier: 2.0+ for very young, tapering off.
        $factor = $age < 22 ? 2.2 : 1.8;
        
        $potential = $overall + ($growthRoom * $factor);
        
        // Market value influence: If market value is high, potential should be higher
        // attr_market is already 1-99.
        if (isset($this->attr_market) && $this->attr_market > $overall) {
            $potential = max($potential, $this->attr_market + 2);
        }

        return min(99, max($overall, (int) $potential));
    }

    protected $fillable = [
        'club_id',
        'first_name',
        'last_name',
        'nationality',
        'photo_path',
        'position',
        'position_main',
        'position_second',
        'position_third',
        'preferred_foot',
        'age',
        'overall',
        'potential',
        'technical',
        'status',
        'market_value',
        'salary',
        'contract_expires_on',
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
        'sofascore_url',
        'player_style',
        'personality_type',
        'attr_attacking',
        'attr_technical',
        'attr_tactical',
        'attr_defending',
        'attr_creativity',
        'attr_market',
        'is_imported',
        'birthday',
        'height',
        'shirt_number',
    ];

    protected function casts(): array
    {
        return [
            'market_value' => 'integer',
            'technical' => 'integer',
            'salary' => 'decimal:2',
            'contract_expires_on' => 'date',
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
            'birthday' => 'date',
            'height' => 'integer',
            'shirt_number' => 'integer',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
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

    public function transferHistory(): HasMany
    {
        return $this->hasMany(PlayerTransferHistory::class)->orderByDesc('transfer_date');
    }

    public function hallOfFameEntries(): HasMany
    {
        return $this->hasMany(ClubHallOfFame::class);
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

        // Cache resolved Storage URLs for the request lifetime — Storage::url() can be
        // expensive when called hundreds of times per live-state poll (44 players × N actions).
        static $urlCache = [];
        return $urlCache[$this->photo_path] ??= Storage::url($this->photo_path);
    }

    public static function mapPosition(?string $position): ?string
    {
        if (!$position) {
            return null;
        }

        return \App\Constants\PlayerPosition::map($position);
    }

    public function getDisplayPositionAttribute(): string
    {
        return self::mapPosition($this->position) ?? (string) $this->position;
    }

    public function getPositionLongAttribute(): string
    {
        return \App\Constants\PlayerPosition::labels()[$this->position] ?? (string) ($this->position ?? '');
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
        $slug = \Illuminate\Support\Str::slug($this->full_name);
        return "https://www.sofascore.com/football/player/{$slug}/{$this->sofascore_id}";
    }

    public function getNationalityCodeAttribute(): ?string
    {
        return \App\Constants\Nationality::getCode($this->nationality);
    }
}
