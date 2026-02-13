<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\GameNotification;
use App\Models\Player;

class MatchAftermathService
{
    public function __construct(
        private readonly PlayerAvailabilityService $availabilityService
    ) {
    }

    public function apply(GameMatch $match): void
    {
        $changes = $this->availabilityService->applyMatchConsequences($match);
        if ($changes === []) {
            return;
        }

        if (!(bool) config('simulation.aftermath.notifications.enabled', true)) {
            return;
        }

        $players = Player::query()
            ->with('club')
            ->whereIn('id', array_values(array_unique(array_map(
                static fn (array $change): int => (int) $change['player_id'],
                $changes
            ))))
            ->get()
            ->keyBy('id');

        foreach ($changes as $change) {
            /** @var Player|null $player */
            $player = $players->get((int) $change['player_id']);
            if (!$player || !$player->club || !(int) $player->club->user_id) {
                continue;
            }

            $clubId = (int) $player->club->id;
            $managerId = (int) $player->club->user_id;
            $contextLabel = $this->contextLabel((string) $change['context']);

            $messages = [];
            if ((int) $change['injury_assigned'] > 0) {
                $messages[] = 'Verletzung: '.(int) $change['injury_after'].' Spiel(e) Ausfall';
            }
            $genericSuspensionAssigned = max(
                0,
                (int) $change['suspension_assigned'] - (int) ($change['yellow_suspension_assigned'] ?? 0)
            );
            if ($genericSuspensionAssigned > 0) {
                $messages[] = 'Sperre ('.$contextLabel.'): '.(int) $change['suspension_after'].' Spiel(e)';
            }
            if ((int) ($change['yellow_suspension_assigned'] ?? 0) > 0) {
                $messages[] = 'Gelb-Sperre ('.$contextLabel.'): '
                    .(int) $change['yellow_suspension_assigned']
                    .' Spiel(e) nach '
                    .(int) ($change['yellow_threshold'] ?? 0)
                    .' Gelben';
            }

            if ($messages !== []) {
                GameNotification::query()->create([
                    'user_id' => $managerId,
                    'club_id' => $clubId,
                    'type' => 'match_aftermath',
                    'title' => 'Nachwirkungen nach Spiel',
                    'message' => $player->full_name.': '.implode(' | ', $messages),
                    'action_url' => '/players/'.$player->id.'?club='.$clubId,
                ]);
            }

            if ($this->shouldNotifyContractRisk($player, $change)) {
                GameNotification::query()->create([
                    'user_id' => $managerId,
                    'club_id' => $clubId,
                    'type' => 'contract_attention',
                    'title' => 'Vertragsrisiko bei Ausfall',
                    'message' => $player->full_name.' faellt aus und Vertrag endet am '
                        .$player->contract_expires_on?->format('d.m.Y').'.',
                    'action_url' => '/contracts?club='.$clubId,
                ]);
            }
        }
    }

    /**
     * @param array<string, mixed> $change
     */
    private function shouldNotifyContractRisk(Player $player, array $change): bool
    {
        if (!(bool) config('simulation.aftermath.contract_alert.enabled', true)) {
            return false;
        }

        if ((int) ($change['injury_assigned'] ?? 0) < 1 && (int) ($change['suspension_assigned'] ?? 0) < 1) {
            return false;
        }

        if (!$player->contract_expires_on) {
            return false;
        }

        $threshold = max(0, (int) config('simulation.aftermath.contract_alert.days_threshold', 120));
        $daysUntilExpiry = now()->startOfDay()->diffInDays($player->contract_expires_on->startOfDay(), false);

        return $daysUntilExpiry <= $threshold;
    }

    private function contextLabel(string $context): string
    {
        return match ($context) {
            CompetitionContextService::LEAGUE => 'Liga',
            CompetitionContextService::CUP_NATIONAL => 'Nationaler Pokal',
            CompetitionContextService::CUP_INTERNATIONAL => 'Internationaler Pokal',
            CompetitionContextService::FRIENDLY => 'Freundschaft',
            default => 'Wettbewerb',
        };
    }
}
