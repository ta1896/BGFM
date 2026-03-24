<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TrainingGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'name',
        'color',
        'notes',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'training_group_player')->withTimestamps();
    }

    public function sessions(): BelongsToMany
    {
        return $this->belongsToMany(TrainingSession::class, 'training_session_group')->withTimestamps();
    }
}
