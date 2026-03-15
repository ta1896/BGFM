<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'club_id',
        'user_id',
        'topic',
        'approach',
        'outcome',
        'happiness_delta',
        'happiness_after',
        'manager_message',
        'player_response',
        'summary',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
