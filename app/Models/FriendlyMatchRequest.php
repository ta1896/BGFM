<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FriendlyMatchRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'challenger_club_id',
        'challenged_club_id',
        'requested_by_user_id',
        'accepted_match_id',
        'kickoff_at',
        'stadium_club_id',
        'status',
        'message',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'kickoff_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function challengerClub(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'challenger_club_id');
    }

    public function challengedClub(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'challenged_club_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function acceptedMatch(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'accepted_match_id');
    }

    public function stadiumClub(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'stadium_club_id');
    }
}
