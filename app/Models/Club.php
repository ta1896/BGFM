<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Club extends Model
{
    use HasFactory;
    
    protected $appends = ['logo_url'];

    protected $fillable = [
        'user_id',
        'is_cpu',
        'name',
        'slug',
        'short_name',
        'logo_path',
        'country',
        'league',
        'league_id',
        'founded_year',
        'reputation',
        'fan_mood',
        'fanbase',
        'board_confidence',
        'training_level',
        'season_objective',
        'captain_player_id',
        'vice_captain_player_id',
        'budget',
        'coins',
        'wage_budget',
        'notes',
        'rival_id_1',
        'rival_id_2',
        'transfermarkt_id',
        'transfermarkt_url',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'coins' => 'integer',
            'wage_budget' => 'decimal:2',
            'fanbase' => 'integer',
            'board_confidence' => 'integer',
            'training_level' => 'integer',
            'is_cpu' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rival1(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'rival_id_1');
    }

    public function rival2(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'rival_id_2');
    }

    public function isRival(int $otherClubId): bool
    {
        return $this->rival_id_1 === $otherClubId || $this->rival_id_2 === $otherClubId;
    }

    public function captain(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'captain_player_id');
    }

    public function viceCaptain(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'vice_captain_player_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function lineups(): HasMany
    {
        return $this->hasMany(Lineup::class);
    }

    public function homeMatches(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'home_club_id');
    }

    public function awayMatches(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'away_club_id');
    }

    public function transferListings(): HasMany
    {
        return $this->hasMany(TransferListing::class, 'seller_club_id');
    }

    public function transferBids(): HasMany
    {
        return $this->hasMany(TransferBid::class, 'bidder_club_id');
    }

    public function trainingSessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class);
    }

    public function financialTransactions(): HasMany
    {
        return $this->hasMany(ClubFinancialTransaction::class);
    }

    public function sponsorContracts(): HasMany
    {
        return $this->hasMany(SponsorContract::class);
    }

    public function activeSponsorContract(): HasOne
    {
        return $this->hasOne(SponsorContract::class)
            ->where('status', 'active')
            ->latest('ends_on');
    }

    public function stadium(): HasOne
    {
        return $this->hasOne(Stadium::class);
    }

    public function stadiumProjects(): HasManyThrough
    {
        return $this->hasManyThrough(StadiumProject::class, Stadium::class);
    }

    public function trainingCamps(): HasMany
    {
        return $this->hasMany(TrainingCamp::class);
    }

    public function loanListingsAsLender(): HasMany
    {
        return $this->hasMany(LoanListing::class, 'lender_club_id');
    }

    public function loanBidsAsBorrower(): HasMany
    {
        return $this->hasMany(LoanBid::class, 'borrower_club_id');
    }

    public function loansAsLender(): HasMany
    {
        return $this->hasMany(Loan::class, 'lender_club_id');
    }

    public function loansAsBorrower(): HasMany
    {
        return $this->hasMany(Loan::class, 'borrower_club_id');
    }

    public function teamOfTheDayPlayers(): HasMany
    {
        return $this->hasMany(TeamOfTheDayPlayer::class);
    }


    public function friendlyRequestsAsChallenger(): HasMany
    {
        return $this->hasMany(FriendlyMatchRequest::class, 'challenger_club_id');
    }

    public function friendlyRequestsAsChallenged(): HasMany
    {
        return $this->hasMany(FriendlyMatchRequest::class, 'challenged_club_id');
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(ClubAchievement::class);
    }

    public function scoutingWatchlists(): HasMany
    {
        return $this->hasMany(ScoutingWatchlist::class);
    }

    public function scoutingReports(): HasMany
    {
        return $this->hasMany(ScoutingReport::class);
    }

    public function getLogoUrlAttribute(): string
    {
        if (!$this->logo_path) {
            return asset('images/placeholders/club.svg');
        }

        if (str_starts_with($this->logo_path, 'http://') || str_starts_with($this->logo_path, 'https://')) {
            return $this->logo_path;
        }

        $normalizedPath = ltrim(preg_replace('#^public/#', '', $this->logo_path), '/');

        if (Storage::disk('public')->exists($normalizedPath)) {
            return Storage::disk('public')->url($normalizedPath);
        }

        // Legacy fallback: older uploads were stored on the default "local" disk.
        if (Storage::disk('local')->exists($this->logo_path)) {
            Storage::disk('public')->put($normalizedPath, Storage::disk('local')->get($this->logo_path));

            return Storage::disk('public')->url($normalizedPath);
        }

        return asset('images/placeholders/club.svg');
    }
}
