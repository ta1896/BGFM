<?php

namespace App\Services;

use App\Models\GameNotification;
use App\Models\Player;
use App\Models\PlayerConversation;
use App\Models\PlayerPlaytimePromise;
use Illuminate\Support\Arr;

class PlayerConversationService
{
    public function __construct(
        private readonly PlayerMoraleService $playerMoraleService,
        private readonly PlayerLoadService $playerLoadService,
    ) {
    }

    /**
     * @param array{topic:string,approach:string,manager_message:?string} $payload
     */
    public function logConversation(Player $player, int $userId, array $payload): PlayerConversation
    {
        $player->loadMissing(['playtimePromises', 'injuries']);

        $topic = $payload['topic'];
        $approach = $payload['approach'];
        $managerMessage = trim((string) ($payload['manager_message'] ?? ''));
        $state = $this->conversationState($player);
        $delta = $this->resolveDelta($topic, $approach, $state);
        $outcome = $this->resolveOutcome($delta);
        $messages = $this->resolveMessages($topic, $approach, $state, $outcome);

        $baseMorale = max(1, min(100, (int) $player->morale + $delta));
        $player->forceFill([
            'morale' => $baseMorale,
            'last_morale_reason' => $messages['summary'],
        ])->save();

        $morale = $this->playerMoraleService->refresh($player->fresh()->loadMissing(['playtimePromises', 'injuries']));

        $conversation = PlayerConversation::query()->create([
            'player_id' => $player->id,
            'club_id' => $player->club_id,
            'user_id' => $userId,
            'topic' => $topic,
            'approach' => $approach,
            'outcome' => $outcome,
            'happiness_delta' => $delta,
            'happiness_after' => (int) $morale['happiness'],
            'manager_message' => $managerMessage !== '' ? $managerMessage : $messages['manager'],
            'player_response' => $messages['response'],
            'summary' => $messages['summary'],
        ]);

        if (in_array($outcome, ['tense', 'fractured'], true)) {
            $this->notifyEscalation($player, $conversation);
        }

        return $conversation;
    }

    /**
     * @return array{promise_pressure:int,fatigue:int,happiness:int,injury_risk:int,role_mismatch:int,active_promise:?PlayerPlaytimePromise}
     */
    private function conversationState(Player $player): array
    {
        /** @var PlayerPlaytimePromise|null $activePromise */
        $activePromise = $player->playtimePromises
            ->sortByDesc('id')
            ->first(fn (PlayerPlaytimePromise $promise) => in_array($promise->status, ['active', 'at_risk', 'broken'], true));

        $promisePressure = $activePromise
            ? max(0, (int) $activePromise->expected_minutes_share - (int) $activePromise->fulfilled_ratio)
            : 0;

        return [
            'promise_pressure' => $promisePressure,
            'fatigue' => (int) $player->fatigue,
            'happiness' => (int) $player->happiness,
            'injury_risk' => $this->playerLoadService->injuryRisk($player),
            'role_mismatch' => max(0, (int) $player->expected_playtime - ($activePromise ? (int) $activePromise->fulfilled_ratio : (int) $player->expected_playtime)),
            'active_promise' => $activePromise,
        ];
    }

    /**
     * @param array{promise_pressure:int,fatigue:int,happiness:int,injury_risk:int,role_mismatch:int,active_promise:?PlayerPlaytimePromise} $state
     */
    private function resolveDelta(string $topic, string $approach, array $state): int
    {
        $delta = match ($topic) {
            'role' => $state['role_mismatch'] > 16
                ? match ($approach) {
                    'supportive' => 7,
                    'honest' => 3,
                    'demanding' => -6,
                    default => 0,
                }
                : match ($approach) {
                    'supportive' => 3,
                    'honest' => 2,
                    'demanding' => -2,
                    default => 0,
                },
            'playtime' => $state['promise_pressure'] > 15
                ? match ($approach) {
                    'honest' => 5,
                    'supportive' => 2,
                    'demanding' => -7,
                    default => 0,
                }
                : match ($approach) {
                    'honest' => 2,
                    'supportive' => 3,
                    'demanding' => -3,
                    default => 0,
                },
            'load' => $state['fatigue'] >= 60 || $state['injury_risk'] >= 55
                ? match ($approach) {
                    'protective' => 6,
                    'honest' => 3,
                    'demanding' => -8,
                    default => 0,
                }
                : match ($approach) {
                    'protective' => 2,
                    'honest' => 1,
                    'demanding' => -4,
                    default => 0,
                },
            'morale' => $state['happiness'] < 50
                ? match ($approach) {
                    'supportive' => 6,
                    'honest' => 3,
                    'demanding' => -5,
                    default => 0,
                }
                : match ($approach) {
                    'supportive' => 2,
                    'honest' => 1,
                    'demanding' => -2,
                    default => 0,
                },
            default => 0,
        };

        if ($state['happiness'] <= 35 && $approach === 'demanding') {
            $delta -= 2;
        }

        if ($state['happiness'] >= 75 && in_array($approach, ['supportive', 'honest'], true)) {
            $delta += 1;
        }

        return max(-10, min(10, $delta));
    }

    private function resolveOutcome(int $delta): string
    {
        return match (true) {
            $delta >= 6 => 'breakthrough',
            $delta >= 2 => 'positive',
            $delta >= -1 => 'steady',
            $delta >= -5 => 'tense',
            default => 'fractured',
        };
    }

    /**
     * @param array{promise_pressure:int,fatigue:int,happiness:int,injury_risk:int,role_mismatch:int,active_promise:?PlayerPlaytimePromise} $state
     * @return array{manager:string,response:string,summary:string}
     */
    private function resolveMessages(string $topic, string $approach, array $state, string $outcome): array
    {
        $manager = match ($topic) {
            'role' => match ($approach) {
                'supportive' => 'Ich sehe deinen Platz in der Gruppe und will dir die Rolle klarer geben.',
                'honest' => 'Ich bin offen: Deine Rolle haengt an Leistung und Balance im Kader.',
                default => 'Ich erwarte, dass du die aktuelle Rollenverteilung akzeptierst und lieferst.',
            },
            'playtime' => match ($approach) {
                'supportive' => 'Ich will deine Minuten sauber steuern und dich naeher an die Mannschaft ziehen.',
                'honest' => 'Ich verspreche dir nur Minuten, die ich realistisch halten kann.',
                default => 'Minuten musst du dir im Training und Spieltag verdienen.',
            },
            'load' => match ($approach) {
                'protective' => 'Wir nehmen Last raus, damit du stabil durch die Phase kommst.',
                'honest' => 'Ich will dich verfuegbar halten und deshalb jetzt sauber dosieren.',
                default => 'Du musst trotz Belastung bereit sein, wenn die Mannschaft dich braucht.',
            },
            default => match ($approach) {
                'supportive' => 'Ich sehe deine Situation und will die Stimmung aktiv drehen.',
                'honest' => 'Ich rede offen mit dir: Die Lage ist nicht ideal, aber loesbar.',
                default => 'Ich brauche jetzt Charakter und Professionalitaet von dir.',
            },
        };

        $response = match ($outcome) {
            'breakthrough' => 'Der Spieler zieht sichtbar mit und fuehlt sich ernst genommen.',
            'positive' => 'Der Spieler reagiert positiv und wirkt deutlich ruhiger.',
            'steady' => 'Das Gespraech beruhigt die Lage nur teilweise.',
            'tense' => 'Der Spieler nimmt das Gespraech kritisch auf und bleibt angespannt.',
            default => 'Das Gespraech eskaliert. Der Spieler fuehlt sich klar unfair behandelt.',
        };

        $summary = match ($topic) {
            'role' => $state['role_mismatch'] > 16
                ? 'Rollengespraech zur unklaren Einsatzrolle.'
                : 'Rollengespraech zur Einordnung im Kader.',
            'playtime' => $state['promise_pressure'] > 15
                ? 'Gespraech ueber drohenden Minutenkonflikt.'
                : 'Gespraech ueber erwartete Einsatzzeit.',
            'load' => $state['fatigue'] >= 60 || $state['injury_risk'] >= 55
                ? 'Medical-Gespraech wegen hoher Belastung.'
                : 'Belastungsgespraech zur Steuerung des Spielers.',
            default => 'Stimmungsgespraech zur aktuellen Lage im Kader.',
        };

        return [
            'manager' => $manager,
            'response' => $response,
            'summary' => $summary,
        ];
    }

    private function notifyEscalation(Player $player, PlayerConversation $conversation): void
    {
        $club = $player->club;
        if (!$club || !(int) $club->user_id) {
            return;
        }

        GameNotification::query()->create([
            'user_id' => (int) $club->user_id,
            'club_id' => (int) $club->id,
            'type' => 'conversation_tense',
            'title' => 'Spielergespaech ist gekippt',
            'message' => $player->full_name.' reagiert angespannt auf das Gespraech ueber '.$this->topicLabel($conversation->topic).'.',
            'action_url' => '/players/'.$player->id.'?club='.$club->id,
        ]);
    }

    public function topicLabel(string $topic): string
    {
        return Arr::get([
            'role' => 'Rolle',
            'playtime' => 'Spielzeit',
            'load' => 'Belastung',
            'morale' => 'Stimmung',
        ], $topic, $topic);
    }

    public function approachLabel(string $approach): string
    {
        return Arr::get([
            'supportive' => 'Supportiv',
            'honest' => 'Offen',
            'demanding' => 'Hart',
            'protective' => 'Vorsichtig',
        ], $approach, $approach);
    }
}
