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
        'training_type_id',
        'training_type_name',
        'type',
        'team_focus',
        'unit_focus',
        'intensity',
        'focus_position',
        'unit_groups',
        'effect_blueprint',
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
            'unit_groups' => 'array',
            'effect_blueprint' => 'array',
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

    public function trainingType(): BelongsTo
    {
        return $this->belongsTo(TrainingType::class);
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'training_session_player')
            ->withPivot([
                'role',
                'focus_group',
                'primary_focus',
                'secondary_focus',
                'individual_intensity',
                'stamina_delta',
                'morale_delta',
                'overall_delta',
                'attribute_deltas',
            ])
            ->withTimestamps();
    }

    public function trainingGroups(): BelongsToMany
    {
        return $this->belongsToMany(TrainingGroup::class, 'training_session_group')->withTimestamps();
    }

    protected function sessionDateFormatted(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => $this->session_date?->format('d.m.Y')
        );
    }
}
