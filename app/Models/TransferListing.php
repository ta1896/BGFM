<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransferListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'seller_club_id',
        'listed_by_user_id',
        'min_price',
        'buy_now_price',
        'listed_at',
        'expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'listed_at' => 'datetime',
            'expires_at' => 'datetime',
            'min_price' => 'decimal:2',
            'buy_now_price' => 'decimal:2',
        ];
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function sellerClub(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'seller_club_id');
    }

    public function listedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'listed_by_user_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(TransferBid::class)->orderByDesc('amount');
    }
}
