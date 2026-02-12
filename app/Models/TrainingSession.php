<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TrainingSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'created_by_user_id',
        'type',
        'intensity',
        'focus_position',
        'session_date',
        'morale_effect',
        'stamina_effect',
        'form_effect',
        'notes',
        'is_applied',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'is_applied' => 'boolean',
            'applied_at' => 'datetime',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'training_session_player')
            ->withPivot(['role', 'stamina_delta', 'morale_delta', 'overall_delta'])
            ->withTimestamps();
    }
}
