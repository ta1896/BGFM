<?php

namespace App\Services\MatchEngine\Builders;

use App\Models\Player;
use Illuminate\Support\Collection;
use App\Services\MatchEngine\EngineConfiguration;

class CardEventBuilder
{
    public function create(int $minutes, int $clubId, Collection $squad, string $type = 'yellow_card'): array
    {
        // Pick a player who is likely to commit a foul (Low defense, high aggression if we had it, low composure)
        // Since we only have 'defending' attribute readily available in basic stats:
        // Lower defending might mean clumsier tackles? Or higher defending means more risks?
        // Let's assume lower defending + position (Defenders/DMs) makes them prone to cards.

        /** @var Player $player */
        $player = $this->weightedPlayerPick($squad, function (Player $p) {
            // Boost probability for Defenders and DMs
            $posFactor = 1.0;
            if (in_array($p->position, ['CB', 'LB', 'RB', 'CDM'])) {
                $posFactor = 2.0;
            }

            // Inverse of defending (clumsy) + aggressive checks (simulated randomness)
            return (110 - $p->defending) * $posFactor;
        });

        return [
            'minute' => mt_rand(1, $minutes),
            'second' => mt_rand(0, 59),
            'club_id' => $clubId,
            'player_id' => $player->id,
            'event_type' => $type,
            'metadata' => null,
        ];
    }

    /**
     * @param Collection<int, Player> $squad
     */
    private function weightedPlayerPick(Collection $squad, callable $weightResolver): Player
    {
        $total = max(1, (int)$squad->sum($weightResolver));
        $hit = mt_rand(1, $total);
        $cursor = 0;

        foreach ($squad as $player) {
            $cursor += max(1, (int)$weightResolver($player));
            if ($cursor >= $hit) {
                return $player;
            }
        }

        /** @var Player|null $fallback */
        $fallback = $squad->first();

        if (!$fallback) {
            throw new \RuntimeException('Cannot pick player from empty squad');
        }

        return $fallback;
    }
}
