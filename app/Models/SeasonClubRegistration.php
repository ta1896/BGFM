<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeasonClubRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_season_id',
        'club_id',
        'squad_limit',
        'wage_cap',
    ];

    protected function casts(): array
    {
        return [
            'wage_cap' => 'decimal:2',
        ];
    }

    public function competitionSeason(): BelongsTo
    {
        return $this->belongsTo(CompetitionSeason::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
