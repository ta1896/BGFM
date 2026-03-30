<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\GameNotification;
use App\Models\MatchPlayerStat;
use App\Models\Player;
use App\Models\PlayerPlaytimePromise;
use Illuminate\Support\Facades\DB;

class MatchAftermathService
{
    public function __construct(
        private readonly PlayerAvailabilityService $availabilityService,
        private readonly PlayerLoadService $playerLoadService,
        private readonly PlayerMoraleService $playerMoraleService,
    ) {
    }

    public function apply(GameMatch $match): void
    {
        $changes = $this->availabilityService->applyMatchConsequences($match);
        $this->applyMatchLoadAndPromises($match);

        if ($changes === [] && !$this->hasPlayersWithStats($match)) {
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

    private function applyMatchLoadAndPromises(GameMatch $match): void
    {
        $stats = MatchPlayerStat::query()
            ->with('player')
            ->where('match_id', $match->id)
            ->get();

        if ($stats->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($stats, $match): void {
            foreach ($stats as $stat) {
                $player = $stat->player;
                if (!$player) {
                    continue;
                }

                $this->playerLoadService->applyMatchLoad($player, $stat, $match);
                $this->refreshActivePromise($player);
                // Unset cached relations so loadMissing fetches the post-update state;
                // avoids a redundant full-model reload via fresh().
                $player->unsetRelation('playtimePromises')->unsetRelation('injuries');
                $this->playerMoraleService->refresh($player->loadMissing(['playtimePromises', 'injuries']));
            }
        });
    }

    private function refreshActivePromise(Player $player): void
    {
        /** @var PlayerPlaytimePromise|null $promise */
        $promise = $player->playtimePromises()
            ->whereIn('status', ['active', 'at_risk'])
            ->latest('id')
            ->first();

        if (!$promise) {
            return;
        }

        $recentStats = MatchPlayerStat::query()
            ->where('player_id', $player->id)
            ->latest('id')
            ->limit(8)
            ->get(['minutes_played']);

        $ratio = $recentStats->isEmpty()
            ? 0
            : max(0, min(100, (int) round((((float) $recentStats->avg('minutes_played')) / 90) * 100)));

        $deadlinePassed = $promise->deadline_at && now()->greaterThan($promise->deadline_at);
        $previousStatus = (string) $promise->status;
        $nextStatus = $deadlinePassed
            ? ($ratio >= (int) $promise->expected_minutes_share ? 'fulfilled' : 'broken')
            : ($ratio + 10 < (int) $promise->expected_minutes_share ? 'at_risk' : 'active');

        $promise->forceFill([
            'fulfilled_ratio' => $ratio,
            'status' => $nextStatus,
        ])->save();

        if ($previousStatus !== $nextStatus) {
            $this->notifyPromiseEscalation($player, $promise, $nextStatus);
        }
    }

    private function hasPlayersWithStats(GameMatch $match): bool
    {
        return MatchPlayerStat::query()->where('match_id', $match->id)->exists();
    }

    private function notifyPromiseEscalation(Player $player, PlayerPlaytimePromise $promise, string $status): void
    {
        $club = $player->club;
        if (!$club || !(int) $club->user_id) {
            return;
        }

        $notification = match ($status) {
            'at_risk' => [
                'type' => 'promise_at_risk',
                'title' => 'Spielzeitversprechen unter Druck',
                'message' => $player->full_name
                    .' liegt bei '
                    .(int) $promise->fulfilled_ratio
                    .'% statt zugesagten '
                    .(int) $promise->expected_minutes_share
                    .'%. Das Versprechen droht zu kippen.',
            ],
            'broken' => [
                'type' => 'promise_broken',
                'title' => 'Spielzeitversprechen gebrochen',
                'message' => $player->full_name
                    .' hat nur '
                    .(int) $promise->fulfilled_ratio
                    .'% statt zugesagten '
                    .(int) $promise->expected_minutes_share
                    .'% erreicht.',
            ],
            'fulfilled' => [
                'type' => 'promise_fulfilled',
                'title' => 'Spielzeitversprechen erfuellt',
                'message' => $player->full_name
                    .' hat das Spielzeitversprechen mit '
                    .(int) $promise->fulfilled_ratio
                    .'% erfuellt.',
            ],
            default => null,
        };

        if (!$notification) {
            return;
        }

        GameNotification::query()->create([
            'user_id' => (int) $club->user_id,
            'club_id' => (int) $club->id,
            'type' => $notification['type'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'action_url' => '/players/'.$player->id.'?club='.$club->id,
        ]);
    }
}
