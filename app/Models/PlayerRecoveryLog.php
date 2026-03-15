<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerRecoveryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'club_id',
        'day',
        'training_load',
        'match_load',
        'fatigue_before',
        'fatigue_after',
        'sharpness_before',
        'sharpness_after',
        'injury_risk',
    ];

    protected function casts(): array
    {
        return [
            'day' => 'date',
        ];
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
