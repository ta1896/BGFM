<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Player;
use App\Models\Club;

class ClubHallOfFame extends Model
{
    use HasFactory;

    protected $table = 'club_hall_of_fame';

    protected $fillable = [
        'player_id',
        'club_id',
        'inducted_at',
        'legend_type',
        'description',
    ];

    protected $casts = [
        'inducted_at' => 'date',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
