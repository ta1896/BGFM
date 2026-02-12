<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_listing_id',
        'loan_bid_id',
        'player_id',
        'lender_club_id',
        'borrower_club_id',
        'weekly_fee',
        'buy_option_price',
        'starts_on',
        'ends_on',
        'status',
        'buy_option_state',
        'buy_option_decided_at',
        'bought_at',
        'returned_at',
    ];

    protected function casts(): array
    {
        return [
            'weekly_fee' => 'decimal:2',
            'buy_option_price' => 'decimal:2',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'buy_option_decided_at' => 'datetime',
            'bought_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(LoanListing::class, 'loan_listing_id');
    }

    public function bid(): BelongsTo
    {
        return $this->belongsTo(LoanBid::class, 'loan_bid_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function lenderClub(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'lender_club_id');
    }

    public function borrowerClub(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'borrower_club_id');
    }
}
