<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchPlannedSubstitution extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'club_id',
        'player_out_id',
        'player_in_id',
        'planned_minute',
        'score_condition',
        'target_slot',
        'status',
        'executed_minute',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function playerOut(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_out_id');
    }

    public function playerIn(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_in_id');
    }
}
