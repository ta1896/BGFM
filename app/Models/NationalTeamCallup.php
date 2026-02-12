<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NationalTeamCallup extends Model
{
    use HasFactory;

    protected $fillable = [
        'national_team_id',
        'player_id',
        'created_by_user_id',
        'called_up_on',
        'released_on',
        'role',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'called_up_on' => 'date',
            'released_on' => 'date',
        ];
    }

    public function nationalTeam(): BelongsTo
    {
        return $this->belongsTo(NationalTeam::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
