<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'lender_club_id',
        'listed_by_user_id',
        'min_weekly_fee',
        'buy_option_price',
        'loan_months',
        'listed_at',
        'expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'min_weekly_fee' => 'decimal:2',
            'buy_option_price' => 'decimal:2',
            'loan_months' => 'integer',
            'listed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function lenderClub(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'lender_club_id');
    }

    public function listedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'listed_by_user_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(LoanBid::class)->orderByDesc('weekly_fee');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }
}
