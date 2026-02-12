<?php

return [
    'window_enforced' => (bool) env('TRANSFER_WINDOW_ENFORCED', true),

    // Day/Month windows in game calendar.
    'windows' => [
        [
            'label' => 'Winterfenster',
            'start' => '01-01',
            'end' => '01-31',
        ],
        [
            'label' => 'Sommerfenster',
            'start' => '06-15',
            'end' => '09-01',
        ],
    ],
];
