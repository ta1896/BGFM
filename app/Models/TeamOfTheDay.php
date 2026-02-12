<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeamOfTheDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'for_date',
        'competition_season_id',
        'matchday',
        'label',
        'formation',
        'generated_by_user_id',
        'generation_context',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'for_date' => 'date',
        ];
    }

    public function competitionSeason(): BelongsTo
    {
        return $this->belongsTo(CompetitionSeason::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by_user_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(TeamOfTheDayPlayer::class);
    }
}
