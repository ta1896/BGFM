<?php

return [
    'sequence' => [
        'min_per_minute' => 3,
        'max_per_minute' => 5,
    ],

    'possession' => [
        'base_percent' => 50.0,
        'strength_divisor' => 4.0,
        'noise_min' => -5,
        'noise_max' => 5,
        'min_percent' => 22,
        'max_percent' => 78,
        'seconds_min' => 15,
        'seconds_max' => 45,
    ],

    'chance' => [
        'xg_base' => 0.10,
        'xg_strength_divisor' => 400.0,
        'xg_noise_min' => 0,
        'xg_noise_max' => 12,
        'xg_noise_divisor' => 100.0,
        'xg_min' => 0.03,
        'xg_max' => 0.48,
        'big_chance_xg_threshold' => 0.24,
    ],

    'formulas' => [
        'pass' => [
            'base' => 0.70,
            'midpoint' => 60.0,
            'divisor' => 180.0,
            'min' => 0.56,
            'max' => 0.93,
        ],
        'tackle_win' => [
            'base' => 0.50,
            'divisor' => 260.0,
            'min' => 0.25,
            'max' => 0.82,
        ],
        'shot_on_target' => [
            'base' => 0.34,
            'midpoint' => 58.0,
            'divisor' => 220.0,
            'min' => 0.22,
            'max' => 0.78,
        ],
        'save' => [
            'base' => 0.55,
            'skill_divisor' => 300.0,
            'xg_divisor' => 2.5,
            'min' => 0.18,
            'max' => 0.86,
        ],
        'penalty_in_play' => [
            'base' => 0.75,
            'divisor' => 300.0,
            'min' => 0.55,
            'max' => 0.94,
        ],
        'penalty_shootout' => [
            'base' => 0.76,
            'divisor' => 300.0,
            'min' => 0.55,
            'max' => 0.94,
        ],
    ],

    'probabilities' => [
        'tackle_attempt' => 0.45,
        'foul_after_tackle_win' => 0.18,
        'corner_after_shot' => 0.12,
        'big_chance_roll' => 0.38,
        'assist' => 0.68,
        'foul_red_card' => 0.04,
        'foul_yellow_card' => 0.30,
        'penalty_awarded_after_foul' => 0.14,
        'penalty_save_event_in_play' => 0.68,
        'random_injury_per_minute' => 0.012,
        'penalty_save_event_shootout' => 0.66,
        'shootout_coinflip_home_wins' => 0.50,
    ],

    'position_fit' => [
        'main' => 1.00,
        'second' => 0.92,
        'third' => 0.84,
        'foreign' => 0.76,
        'foreign_gk' => 0.55,
    ],

    'live_changes' => [
        'planned_substitutions' => [
            'max_per_club' => 5,
            'min_minutes_ahead' => 2,
            'min_interval_minutes' => 3,
        ],
    ],
];
