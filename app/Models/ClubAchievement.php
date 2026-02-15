<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'competition_season_id',
        'type',
        'title',
        'achieved_at',
    ];

    protected function casts(): array
    {
        return [
            'achieved_at' => 'date',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function competitionSeason(): BelongsTo
    {
        return $this->belongsTo(CompetitionSeason::class);
    }
}
