<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingType extends Model
{
    use HasFactory;

    public const CATEGORY_OPTIONS = [
        'fitness' => 'Fitness',
        'tactics' => 'Taktik',
        'technical' => 'Technik',
        'recovery' => 'Regeneration',
        'friendly' => 'Locker',
    ];

    public const INTENSITY_OPTIONS = [
        'low' => 'Leicht',
        'medium' => 'Normal',
        'high' => 'Hoch',
    ];

    public const TONE_OPTIONS = [
        'amber' => 'Amber',
        'emerald' => 'Emerald',
        'cyan' => 'Cyan',
        'rose' => 'Rose',
        'violet' => 'Violet',
        'fuchsia' => 'Fuchsia',
    ];

    public const ICON_OPTIONS = [
        'Lightning' => 'Lightning',
        'Target' => 'Target',
        'GraduationCap' => 'GraduationCap',
        'Heartbeat' => 'Heartbeat',
        'Rows' => 'Rows',
        'Users' => 'Users',
    ];

    public const EFFECT_OPTIONS = [
        'morale_effect' => 'Moral',
        'stamina_effect' => 'Ausdauer',
        'form_effect' => 'Overall',
        'technical' => 'Technical',
        'passing' => 'Passing',
        'shooting' => 'Shooting',
        'defending' => 'Defending',
        'pace' => 'Pace',
        'physical' => 'Physical',
        'attr_attacking' => 'Attacking',
        'attr_tactical' => 'Tactical',
        'attr_defending' => 'Defending Attr.',
        'attr_creativity' => 'Creativity',
        'potential' => 'Potential',
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'team_focus',
        'unit_focus',
        'default_intensity',
        'tone',
        'icon',
        'effects',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'effects' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function trainingSessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class);
    }
}
