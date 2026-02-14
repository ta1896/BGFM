<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

class Lineup extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'match_id',
        'name',
        'formation',
        'mentality',
        'aggression',
        'line_height',
        'offside_trap',
        'time_wasting',
        'attack_focus',
        'penalty_taker_player_id',
        'free_kick_near_player_id',
        'free_kick_far_player_id',
        'corner_left_taker_player_id',
        'corner_right_taker_player_id',
        'is_active',
        'is_template',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_template' => 'boolean',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function match (): BelongsTo
    {
        return $this->belongsTo(GameMatch::class , 'match_id');
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class)
            ->withPivot([
            'pitch_position',
            'sort_order',
            'x_coord',
            'y_coord',
            'is_captain',
            'is_set_piece_taker',
            'is_bench',
            'bench_order',
        ])
            ->withTimestamps()
            ->orderByPivot('is_bench')
            ->orderByPivot('sort_order');
    }

    public function penaltyTaker(): BelongsTo
    {
        return $this->belongsTo(Player::class , 'penalty_taker_player_id');
    }

    public function freeKickTaker(): BelongsTo
    {
        return $this->belongsTo(Player::class , 'free_kick_taker_player_id');
    }

    public function cornerLeftTaker(): BelongsTo
    {
        return $this->belongsTo(Player::class , 'corner_left_taker_player_id');
    }

    public function cornerRightTaker(): BelongsTo
    {
        return $this->belongsTo(Player::class , 'corner_right_taker_player_id');
    }
}
