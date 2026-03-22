<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerTransferHistory extends Model
{
    protected $fillable = [
        'player_id',
        'season',
        'transfer_date',
        'left_club_name',
        'left_club_tm_id',
        'left_club_id',
        'joined_club_name',
        'joined_club_tm_id',
        'joined_club_id',
        'market_value',
        'fee',
        'is_loan',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'market_value' => 'integer',
        'is_loan' => 'boolean',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function leftClub(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'left_club_id');
    }

    public function joinedClub(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'joined_club_id');
    }
}
