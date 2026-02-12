<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferBid extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_listing_id',
        'bidder_club_id',
        'bidder_user_id',
        'amount',
        'offer_player_id',
        'message',
        'status',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'decided_at' => 'datetime',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(TransferListing::class, 'transfer_listing_id');
    }

    public function bidderClub(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'bidder_club_id');
    }

    public function bidderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bidder_user_id');
    }

    public function offerPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'offer_player_id');
    }
}
