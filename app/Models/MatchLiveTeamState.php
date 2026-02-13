<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchLiveTeamState extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'club_id',
        'possession_seconds',
        'actions_count',
        'dangerous_attacks',
        'pass_attempts',
        'pass_completions',
        'tackle_attempts',
        'tackle_won',
        'fouls_committed',
        'corners_won',
        'shots',
        'shots_on_target',
        'expected_goals',
        'yellow_cards',
        'red_cards',
        'substitutions_used',
        'tactical_changes_count',
        'last_tactical_change_minute',
        'last_substitution_minute',
        'tactical_style',
        'phase',
        'current_ball_carrier_player_id',
        'last_set_piece_taker_player_id',
        'last_set_piece_type',
        'last_set_piece_minute',
    ];

    protected function casts(): array
    {
        return [
            'expected_goals' => 'decimal:2',
            'current_ball_carrier_player_id' => 'integer',
            'last_set_piece_taker_player_id' => 'integer',
            'last_set_piece_minute' => 'integer',
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
