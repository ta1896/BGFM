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

    'positions' => [
        'aliases' => [
            'GK' => 'TW',
            'LB' => 'LV',
            'CB' => 'IV',
            'RB' => 'RV',
            'LWB' => 'LV',
            'RWB' => 'RV',
            'CDM' => 'DM',
            'CM' => 'ZM',
            'CAM' => 'OM',
            'LAM' => 'OM',
            'ZOM' => 'OM',
            'RAM' => 'OM',
            'LW' => 'LF',
            'RW' => 'RF',
            'ST' => 'MS',
            'CF' => 'HS',
            'LS' => 'MS',
            'RS' => 'MS',
        ],
        'groups' => [
            'TW' => 'GK',
            'GK' => 'GK',
            'LV' => 'DEF',
            'IV' => 'DEF',
            'RV' => 'DEF',
            'LWB' => 'DEF',
            'RWB' => 'DEF',
            'LM' => 'MID',
            'ZM' => 'MID',
            'RM' => 'MID',
            'DM' => 'MID',
            'OM' => 'MID',
            'LAM' => 'MID',
            'ZOM' => 'MID',
            'RAM' => 'MID',
            'LS' => 'FWD',
            'MS' => 'FWD',
            'RS' => 'FWD',
            'ST' => 'FWD',
            'LW' => 'FWD',
            'RW' => 'FWD',
            'LF' => 'FWD',
            'RF' => 'FWD',
            'HS' => 'FWD',
            'DEF' => 'DEF',
            'MID' => 'MID',
            'FWD' => 'FWD',
        ],
        'slot_aliases' => [
            'TW' => ['TW', 'GK'],
            'LV' => ['LV', 'LWB'],
            'RV' => ['RV', 'RWB'],
            'LWB' => ['LWB', 'LV', 'LM'],
            'RWB' => ['RWB', 'RV', 'RM'],
            'IV' => ['IV'],
            'LM' => ['LM', 'LWB', 'LV', 'LF'],
            'RM' => ['RM', 'RWB', 'RV', 'RF'],
            'DM' => ['DM', 'ZM'],
            'ZM' => ['ZM', 'DM', 'OM', 'ZOM'],
            'OM' => ['OM', 'ZOM', 'LAM', 'RAM', 'ZM'],
            'ZOM' => ['ZOM', 'OM', 'LAM', 'RAM'],
            'LAM' => ['LAM', 'LM', 'OM', 'ZOM'],
            'RAM' => ['RAM', 'RM', 'OM', 'ZOM'],
            'LF' => ['LF', 'LW', 'LM', 'LS', 'ST', 'MS'],
            'RF' => ['RF', 'RW', 'RM', 'RS', 'ST', 'MS'],
            'LS' => ['LS', 'LF', 'ST', 'MS'],
            'RS' => ['RS', 'RF', 'ST', 'MS'],
            'ST' => ['ST', 'MS', 'HS', 'LS', 'RS'],
            'MS' => ['MS', 'ST', 'HS', 'LS', 'RS'],
            'HS' => ['HS', 'MS', 'ST', 'ZOM'],
        ],
        'group_fallbacks' => [
            'GK' => ['GK'],
            'DEF' => ['DEF', 'MID'],
            'MID' => ['MID', 'DEF', 'FWD'],
            'FWD' => ['FWD', 'MID'],
        ],
    ],

    'lineup_scoring' => [
        'slot_score_bonuses' => [
            'main' => 120.0,
            'second' => 70.0,
            'third' => 35.0,
            'group_fallback' => 20.0,
        ],
        'fit_weight' => 260.0,
        'role_weight' => 3.0,
        'low_fit_penalty' => 220.0,
    ],

    'team_strength' => [
        'weights' => [
            'attack' => [
                'shooting' => 0.18,
                'pace' => 0.08,
                'physical' => 0.05,
                'overall' => 0.14,
                'attr_attacking' => 0.22,
                'attr_technical' => 0.12,
                'attr_tactical' => 0.05,
                'attr_creativity' => 0.10,
                'attr_market' => 0.03,
                'potential' => 0.03,
            ],
            'midfield' => [
                'passing' => 0.12,
                'pace' => 0.05,
                'defending' => 0.06,
                'overall' => 0.12,
                'attr_technical' => 0.18,
                'attr_tactical' => 0.18,
                'attr_creativity' => 0.16,
                'attr_defending' => 0.07,
                'attr_attacking' => 0.03,
                'attr_market' => 0.02,
                'potential' => 0.01,
            ],
            'defense' => [
                'defending' => 0.14,
                'physical' => 0.06,
                'passing' => 0.05,
                'overall' => 0.12,
                'attr_defending' => 0.26,
                'attr_tactical' => 0.16,
                'attr_technical' => 0.10,
                'attr_creativity' => 0.05,
                'attr_market' => 0.03,
                'potential' => 0.03,
            ],
        ],
        'formation_factor' => [
            'complete_lineup' => 1.0,
            'incomplete_lineup' => 0.8,
            'minimum_players' => 8,
        ],
        'chemistry' => [
            'size_bonus_cap' => 10,
            'fit_modifier_min' => 0.82,
            'fit_modifier_max' => 1.0,
        ],
    ],

    'match_strength' => [
        'weights' => [
            'overall' => 0.16,
            'shooting' => 0.06,
            'passing' => 0.06,
            'defending' => 0.06,
            'stamina' => 0.05,
            'morale' => 0.05,
            'attr_attacking' => 0.12,
            'attr_technical' => 0.10,
            'attr_tactical' => 0.10,
            'attr_defending' => 0.10,
            'attr_creativity' => 0.07,
            'attr_market' => 0.04,
            'potential' => 0.03,
        ],
        'home_bonus' => 3.5,
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

    'features' => [
        'player_conversations_enabled' => false,
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
