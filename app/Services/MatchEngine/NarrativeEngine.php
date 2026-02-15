<?php

namespace App\Services\MatchEngine;

use App\Models\MatchTickerTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class NarrativeEngine
{
    /**
     * Generate a dynamic narrative text for a given event.
     */
    public function generate(string $eventType, array $data, string $locale = 'de', array $usedIds = [], string $mood = 'neutral'): string
    {
        $template = $this->pickTemplate($eventType, $locale, $mood, $usedIds);

        if (!$template) {
            return $this->getFallbackText($eventType, $data);
        }

        return $this->replaceTokens($template->text, $data);
    }

    /**
     * Pick a random template for the given event type, avoiding already used ones.
     */
    public function pickTemplate(string $eventType, string $locale, string $mood = 'neutral', array $usedIds = []): ?MatchTickerTemplate
    {
        // Cache name including mood
        $cacheKey = "ticker_templates_{$eventType}_{$locale}_{$mood}";

        $templates = \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () use ($eventType, $locale, $mood) {
            $query = MatchTickerTemplate::where('event_type', $eventType)
                ->where('locale', $locale);

            // If mood is not neutral, we try to fetch specific mood templates + neutral ones. 
            // If mood IS neutral (default), we fetch ALL templates to have variety, 
            // because "neutral" in simulation context just means "no specific preference".
            if ($mood !== 'neutral') {
                $query->whereIn('mood', [$mood, 'neutral']);
            }
            // If neutral, we don't add a mood where-clause, so we get all moods (excited, sad, etc.)
            // The logic below (lines 51+) will prioritize if we had a preference, but for neutral we don't care.

            return $query->get();
        });

        if ($templates->isEmpty()) {
            return null;
        }

        // Filter by mood preference (try specific mood first)
        if ($mood !== 'neutral') {
            $moodTemplates = $templates->where('mood', $mood);
            if ($moodTemplates->isNotEmpty()) {
                $templates = $moodTemplates;
            }
        }

        // Filter out used IDs if possible
        $available = $templates->whereNotIn('id', $usedIds);

        // Fallback to all templates if everything was already used
        if ($available->isEmpty()) {
            $available = $templates;
        }

        return $available->random();
    }

    /**
     * Replace tokens like {player}, {club}, {score} with actual data.
     */
    public function replaceTokens(string $text, array $data): string
    {
        $replacements = [];

        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $replacements["{{$key}}"] = (string) $value;
            }
        }

        // Add conditional stat logic if data contains stats objects
        // This is a placeholder for potential complex logic, but for now we stick to simple replacement
        // derived from the $data array which should be pre-populated by ActionEngine.

        return strtr($text, $replacements);
    }

    /**
     * Provide a basic fallback text if no template is found in the database.
     */
    public function getFallbackText(string $eventType, array $data): string
    {
        $playerName = $data['player'] ?? 'Spieler';
        $clubName = $data['club'] ?? 'Verein';

        return match ($eventType) {
            'goal' => "TOR! {$playerName} trifft für {$clubName}!",
            'yellow_card' => "Gelbe Karte für {$playerName}.",
            'red_card' => "Platzverweis! Rote Karte für {$playerName}.",
            'substitution' => "Wechsel bei {$clubName}: {$playerName} kommt neu ins Spiel.",
            'foul' => "Foulspiel von {$playerName}.",
            'chance' => "Großchance für {$playerName}!",
            'corner' => "Eckstoß für {$clubName}.",
            'free_kick' => "Freistoß für {$clubName} nach Foul an {$playerName}.",
            'offside' => "Abseits! {$playerName} stand im Abseits.",
            'shot' => "{$playerName} zieht ab! Schuss für {$clubName}.",
            'save' => "Starke Parade! Der Torwart von {$clubName} hält.",
            'injury' => "{$playerName} von {$clubName} liegt am Boden.",
            'midfield_possession' => "Ballbesitz für {$clubName} im Mittelfeld.",
            'turnover' => "Ballverlust von {$playerName}. {$clubName} erobert den Ball.",
            'throw_in' => "Einwurf für {$clubName}.",
            'clearance' => "Klärungsaktion von {$playerName}.",
            default => "Ereignis: {$eventType} durch {$playerName} ({$clubName}).",
        };
    }
}
