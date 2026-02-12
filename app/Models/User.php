<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function clubs(): HasMany
    {
        return $this->hasMany(Club::class);
    }

    public function gameNotifications(): HasMany
    {
        return $this->hasMany(GameNotification::class);
    }

    public function transferListings(): HasMany
    {
        return $this->hasMany(TransferListing::class, 'listed_by_user_id');
    }

    public function transferBids(): HasMany
    {
        return $this->hasMany(TransferBid::class, 'bidder_user_id');
    }

    public function loanListings(): HasMany
    {
        return $this->hasMany(LoanListing::class, 'listed_by_user_id');
    }

    public function loanBids(): HasMany
    {
        return $this->hasMany(LoanBid::class, 'bidder_user_id');
    }

    public function sponsorContractsSigned(): HasMany
    {
        return $this->hasMany(SponsorContract::class, 'signed_by_user_id');
    }

    public function trainingCampsCreated(): HasMany
    {
        return $this->hasMany(TrainingCamp::class, 'created_by_user_id');
    }

    public function managedNationalTeams(): HasMany
    {
        return $this->hasMany(NationalTeam::class, 'manager_user_id');
    }

    public function generatedTeamsOfTheDay(): HasMany
    {
        return $this->hasMany(TeamOfTheDay::class, 'generated_by_user_id');
    }

    public function randomEventsTriggered(): HasMany
    {
        return $this->hasMany(RandomEventOccurrence::class, 'triggered_by_user_id');
    }

    public function friendlyRequests(): HasMany
    {
        return $this->hasMany(FriendlyMatchRequest::class, 'requested_by_user_id');
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }
}
