<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerPlaytimePromise extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'club_id',
        'promise_type',
        'expected_minutes_share',
        'deadline_at',
        'status',
        'fulfilled_ratio',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'deadline_at' => 'datetime',
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
