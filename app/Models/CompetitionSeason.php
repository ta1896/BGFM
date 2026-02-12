<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitionSeason extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'season_id',
        'format',
        'matchdays',
        'points_win',
        'points_draw',
        'points_loss',
        'promoted_slots',
        'relegated_slots',
        'is_finished',
    ];

    protected function casts(): array
    {
        return [
            'is_finished' => 'boolean',
        ];
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(SeasonClubRegistration::class);
    }

    public function statistics(): HasMany
    {
        return $this->hasMany(SeasonClubStatistic::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(GameMatch::class);
    }
}
