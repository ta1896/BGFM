<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RandomEventTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'rarity',
        'min_reputation',
        'max_reputation',
        'budget_delta_min',
        'budget_delta_max',
        'morale_delta',
        'stamina_delta',
        'overall_delta',
        'fan_mood_delta',
        'board_confidence_delta',
        'probability_weight',
        'is_active',
        'description_template',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function occurrences(): HasMany
    {
        return $this->hasMany(RandomEventOccurrence::class, 'template_id');
    }
}
