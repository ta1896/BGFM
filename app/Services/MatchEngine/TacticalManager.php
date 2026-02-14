<?php

namespace App\Services\MatchEngine;

use App\Models\Lineup;

class TacticalManager
{
    public function getTacticalModifiers(?Lineup $lineup): array
    {
        $modifiers = [
            'attack' => 1.0,
            'defense' => 1.0,
            'possession' => 1.0,
            'aggression' => 1.0,
            'card_chance' => 1.0,
            'pressing' => 0.0,
            'offside_success' => 0.0,
            'counter_vulnerability' => 0.0,
        ];

        if (!$lineup) {
            return $modifiers;
        }

        // 1. Mentality (Mentality)
        $mentality = $lineup->mentality ?? 'normal';
        switch ($mentality) {
            case 'defensive':
                $modifiers['attack'] *= 0.80;
                $modifiers['defense'] *= 1.25;
                $modifiers['possession'] *= 0.85;
                break;
            case 'counter':
                $modifiers['attack'] *= 1.10;
                $modifiers['defense'] *= 1.15;
                $modifiers['possession'] *= 0.70;
                $modifiers['counter_vulnerability'] -= 0.10; // Better at absorbing pressure
                break;
            case 'offensive':
                $modifiers['attack'] *= 1.20;
                $modifiers['defense'] *= 0.85;
                $modifiers['possession'] *= 1.05;
                break;
            case 'all_out':
                $modifiers['attack'] *= 1.35;
                $modifiers['defense'] *= 0.65;
                $modifiers['possession'] *= 1.10;
                $modifiers['counter_vulnerability'] += 0.20; // High risk
                break;
            default: // normal
                // Baseline 1.0
                break;
        }

        // 2. Aggression (Härte)
        $aggression = $lineup->aggression ?? 'normal';
        switch ($aggression) {
            case 'cautious':
                $modifiers['aggression'] *= 0.70;
                $modifiers['card_chance'] *= 0.60;
                $modifiers['defense'] *= 0.95; // Less effective tackling
                break;
            case 'aggressive':
                $modifiers['aggression'] *= 1.30;
                $modifiers['card_chance'] *= 1.50;
                $modifiers['defense'] *= 1.05; // Slightly better defense due to intensity
                break;
        }

        // 3. Line Height (Ketten-Höhe)
        $lineHeight = $lineup->line_height ?? 'normal';
        switch ($lineHeight) {
            case 'deep':
                $modifiers['defense'] *= 1.10;
                $modifiers['counter_vulnerability'] -= 0.15;
                $modifiers['pressing'] -= 0.20;
                break;
            case 'high':
                $modifiers['defense'] *= 0.95;
                $modifiers['pressing'] += 0.10;
                $modifiers['counter_vulnerability'] += 0.10;
                break;
            case 'very_high':
                $modifiers['defense'] *= 0.90;
                $modifiers['pressing'] += 0.20;
                $modifiers['counter_vulnerability'] += 0.25;
                break;
        }

        // 4. Offside Trap (Abseitsfalle)
        if ($lineup->offside_trap) {
            $modifiers['offside_success'] += 0.15;
            $modifiers['counter_vulnerability'] += 0.10; // Risky
        }

        // 5. Time Wasting (Zeitspiel)
        if ($lineup->time_wasting) {
            $modifiers['possession'] *= 1.05;
            $modifiers['attack'] *= 0.90; // Slower buildup
        }

        return $modifiers;
    }
}
