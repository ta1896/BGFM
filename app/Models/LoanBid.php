<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanBid extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_listing_id',
        'borrower_club_id',
        'bidder_user_id',
        'weekly_fee',
        'message',
        'status',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'weekly_fee' => 'decimal:2',
            'decided_at' => 'datetime',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(LoanListing::class, 'loan_listing_id');
    }

    public function borrowerClub(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'borrower_club_id');
    }

    public function bidderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bidder_user_id');
    }
}
