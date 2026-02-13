<?php

return [
    'deterministic' => [
        'enabled' => false,
    ],

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

    'lineup' => [
        'max_bench_players' => 5,
    ],

    'cup' => [
        'away_goals_rule' => true,
        'two_legged' => [
            'enabled' => false,
            'min_participants' => 4,
            'max_participants' => 16,
            'days_between_legs' => 7,
        ],
        'rewards' => [
            'enabled' => true,
            'notifications' => [
                'enabled' => true,
            ],
            'advancement' => [
                'default' => 50000,
                'achtelfinale' => 75000,
                'viertelfinale' => 100000,
                'halbfinale' => 150000,
                'finale' => 225000,
            ],
            'champion' => 350000,
        ],
        'qualification' => [
            'enabled' => true,
            'source_league_tier' => 1,
            'slots_by_competition_tier' => [
                1 => 4,
                2 => 2,
            ],
            'auto_generate_fixtures' => true,
        ],
    ],

    'aftermath' => [
        'notifications' => [
            'enabled' => true,
        ],
        'contract_alert' => [
            'enabled' => true,
            'days_threshold' => 120,
        ],
        'yellow_cards' => [
            'enabled' => true,
            'reset_on_season_rollover' => true,
            'default' => [
                'threshold' => 5,
                'suspension_matches' => 1,
            ],
            'league' => [
                'threshold' => 5,
                'suspension_matches' => 1,
            ],
            'cup_national' => [
                'threshold' => 3,
                'suspension_matches' => 1,
            ],
            'cup_international' => [
                'threshold' => 3,
                'suspension_matches' => 1,
            ],
            'friendly' => [
                'threshold' => 99,
                'suspension_matches' => 0,
            ],
        ],
        'injury' => [
            'min_matches' => 1,
            'max_matches' => 4,
        ],
        'suspension' => [
            'default' => [
                'min_matches' => 1,
                'max_matches' => 3,
            ],
            'league' => [
                'min_matches' => 1,
                'max_matches' => 3,
            ],
            'cup_national' => [
                'min_matches' => 1,
                'max_matches' => 3,
            ],
            'cup_international' => [
                'min_matches' => 1,
                'max_matches' => 3,
            ],
            'friendly' => [
                'min_matches' => 1,
                'max_matches' => 2,
            ],
        ],
    ],

    'scheduler' => [
        'interval_minutes' => 1,
        'default_limit' => 0,
        'default_types' => ['friendly', 'league', 'cup'],
        'default_minutes_per_run' => 5,
        'claim_stale_after_seconds' => 180,
        'runner_lock_seconds' => 120,
        'health' => [
            'running_stale_after_seconds' => 600,
            'failed_runs_alert_threshold' => 0,
            'skipped_locked_alert_threshold' => 1,
            'stale_takeovers_alert_threshold' => 0,
            'abandoned_runs_alert_threshold' => 0,
            'check_enabled' => true,
            'check_limit' => 60,
            'check_strict' => true,
            'log_success' => false,
        ],
    ],

    'observers' => [
        'match_finished' => [
            'enabled' => true,
            'rebuild_match_player_stats' => true,
            'aggregate_player_competition_stats' => true,
            'apply_match_availability' => true,
            'update_competition_after_match' => true,
            'settle_match_finance' => true,
        ],
    ],
];
