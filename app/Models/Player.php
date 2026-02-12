<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

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

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
