<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SponsorContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'sponsor_id',
        'signed_by_user_id',
        'weekly_amount',
        'signing_bonus',
        'starts_on',
        'ends_on',
        'status',
        'last_payout_on',
        'objectives',
    ];

    protected function casts(): array
    {
        return [
            'weekly_amount' => 'decimal:2',
            'signing_bonus' => 'decimal:2',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'last_payout_on' => 'date',
            'objectives' => 'array',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Sponsor::class);
    }

    public function signedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by_user_id');
    }
}
