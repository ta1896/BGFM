<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RandomEventOccurrence extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'club_id',
        'player_id',
        'triggered_by_user_id',
        'status',
        'title',
        'message',
        'happened_on',
        'applied_at',
        'effect_payload',
    ];

    protected function casts(): array
    {
        return [
            'happened_on' => 'date',
            'applied_at' => 'datetime',
            'effect_payload' => 'array',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(RandomEventTemplate::class, 'template_id');
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }
}
