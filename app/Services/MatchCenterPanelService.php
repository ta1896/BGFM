<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\ManagerPresence;
use App\Models\Player;
use App\Modules\ModuleManager;
use Illuminate\Support\Collection;

class MatchCenterPanelService
{
    public function build(GameMatch $match, array $state): array
    {
        $registry = app(ModuleManager::class)->frontendRegistry();
        $definitions = collect($registry['matchcenter_panels'] ?? [])
            ->sortBy(fn (array $panel) => (int) ($panel['priority'] ?? 999))
            ->values();

        if ($definitions->isEmpty()) {
            return [];
        }

        $lineupPlayers = $this->lineupPlayers($state);
        $injuredOnPitchCount = collect($state['player_states'] ?? [])->where('is_injured', true)->count();
        $sentOffCount = collect($state['player_states'] ?? [])->where('is_sent_off', true)->count();
        $liveManagerCount = ManagerPresence::query()
            ->where('match_id', $match->id)
            ->where('last_seen_at', '>=', now()->subMinutes(5))
            ->count();

        return $definitions->map(function (array $panel) use ($match, $state, $lineupPlayers, $injuredOnPitchCount, $sentOffCount, $liveManagerCount): array {
            $data = match ($panel['key'] ?? null) {
                'live-center-match-pulse' => [
                    'headline' => (($state['status'] ?? 'scheduled') === 'live' ? 'Live '.$state['live_minute'].'\'' : ($state['status_label'] ?? 'Matchday')),
                    'summary' => $liveManagerCount > 0
                        ? $liveManagerCount.' manager channels active on this fixture.'
                        : 'No active manager presence detected for this fixture.',
                    'stats' => [
                        ['label' => 'Minute', 'value' => (int) ($state['live_minute'] ?? 0)],
                        ['label' => 'Actions', 'value' => count($state['actions'] ?? [])],
                        ['label' => 'Managers', 'value' => $liveManagerCount],
                    ],
                ],
                'medical-center-match-risk' => $this->medicalPanelData($lineupPlayers, $injuredOnPitchCount, $sentOffCount),
                'awards-center-match-awards' => $this->awardsPanelData($match, $state, $lineupPlayers),
                default => [
                    'headline' => $panel['title'] ?? 'Module Panel',
                    'summary' => $panel['description'] ?? '',
                    'stats' => [],
                ],
            };

            return array_merge($panel, ['data' => $data]);
        })->all();
    }

    private function lineupPlayers(array $state): Collection
    {
        $lineupPlayerIds = collect($state['lineups'] ?? [])
            ->flatMap(function (array $lineup): array {
                $starters = collect($lineup['starters'] ?? [])->pluck('id');
                $bench = collect($lineup['bench'] ?? [])->pluck('id');

                return $starters->merge($bench)->filter()->values()->all();
            })
            ->unique()
            ->values();

        if ($lineupPlayerIds->isEmpty()) {
            return collect();
        }

        return Player::query()
            ->with(['injuries' => fn ($query) => $query->where('status', 'active')->latest('id')])
            ->whereIn('id', $lineupPlayerIds)
            ->get(['id', 'club_id', 'first_name', 'last_name', 'photo_path', 'medical_status', 'fatigue'])
            ->keyBy('id');
    }

    private function medicalPanelData(Collection $lineupPlayers, int $injuredOnPitchCount, int $sentOffCount): array
    {
        $critical = $lineupPlayers
            ->filter(function (Player $player): bool {
                $injury = $player->injuries->first();

                return in_array((string) $player->medical_status, ['rehab', 'monitoring', 'risk'], true)
                    || in_array((string) ($injury?->availability_status), ['unavailable', 'bench_only', 'limited'], true)
                    || (int) $player->fatigue >= 75;
            })
            ->sortByDesc(function (Player $player): int {
                $injury = $player->injuries->first();

                return match (true) {
                    (string) ($injury?->availability_status) === 'unavailable' => 5,
                    (string) ($injury?->availability_status) === 'bench_only' => 4,
                    (string) ($injury?->availability_status) === 'limited' => 3,
                    (string) $player->medical_status === 'risk' => 2,
                    default => 1,
                };
            })
            ->take(3)
            ->values();

        return [
            'headline' => $critical->isNotEmpty() ? $critical->count().' medical flags' : 'Matchday green light',
            'summary' => $critical->isNotEmpty()
                ? 'Medical and fatigue warnings are active for selected lineup players.'
                : 'No critical medical restrictions detected in the current lineup pool.',
            'stats' => [
                ['label' => 'Flags', 'value' => $critical->count()],
                ['label' => 'On-pitch injuries', 'value' => $injuredOnPitchCount],
                ['label' => 'Sent off', 'value' => $sentOffCount],
            ],
            'players' => $critical->map(function (Player $player): array {
                $injury = $player->injuries->first();

                return [
                    'id' => $player->id,
                    'name' => $player->full_name,
                    'photo_url' => $player->photo_url,
                    'medical_status' => $player->medical_status,
                    'fatigue' => (int) $player->fatigue,
                    'availability_status' => $injury?->availability_status,
                ];
            })->all(),
        ];
    }

    private function awardsPanelData(GameMatch $match, array $state, Collection $lineupPlayers): array
    {
        $playerDirectory = collect($state['player_states'] ?? [])
            ->mapWithKeys(fn (array $player): array => [
                (int) $player['player_id'] => [
                    'id' => (int) $player['player_id'],
                    'name' => (string) ($player['player_name'] ?? ''),
                    'club_id' => (int) ($player['club_id'] ?? 0),
                    'photo_url' => $player['photo_url'] ?? null,
                ],
            ])
            ->merge(
                $lineupPlayers->mapWithKeys(fn (Player $player): array => [
                    (int) $player->id => [
                        'id' => (int) $player->id,
                        'name' => (string) $player->full_name,
                        'club_id' => (int) ($player->club_id ?? 0),
                        'photo_url' => $player->photo_url,
                    ],
                ])
            );

        $clubs = collect([
            (int) $match->homeClub->id => [
                'id' => (int) $match->homeClub->id,
                'name' => (string) $match->homeClub->name,
                'short_name' => (string) ($match->homeClub->short_name ?: $match->homeClub->name),
                'logo_url' => $match->homeClub->logo_url,
            ],
            (int) $match->awayClub->id => [
                'id' => (int) $match->awayClub->id,
                'name' => (string) $match->awayClub->name,
                'short_name' => (string) ($match->awayClub->short_name ?: $match->awayClub->name),
                'logo_url' => $match->awayClub->logo_url,
            ],
        ]);

        $awards = collect([
            $this->playerOfTheMatchAward($state, $playerDirectory, $clubs),
            $this->turningPointAward($state, $playerDirectory, $clubs, $match),
            $this->saveOfTheGameAward($state, $playerDirectory, $clubs, $match),
        ])->filter()->values();

        if ($awards->isEmpty()) {
            return [
                'headline' => 'Awards pending',
                'summary' => 'Start or finish the match to unlock player of the match, turning point, and save of the game.',
                'stats' => [
                    ['label' => 'Awards', 'value' => 0],
                    ['label' => 'Status', 'value' => ucfirst((string) ($state['status'] ?? 'scheduled'))],
                    ['label' => 'Minute', 'value' => (int) ($state['live_minute'] ?? 0)],
                ],
                'awards' => [],
            ];
        }

        $playerOfMatch = $awards->firstWhere('award_key', 'player_of_the_match');
        $turningPoint = $awards->firstWhere('award_key', 'turning_point');
        $saveOfGame = $awards->firstWhere('award_key', 'save_of_the_game');

        return [
            'headline' => $awards->count().' match awards ready',
            'summary' => 'A compact award layer for standout performances and key moments from this fixture.',
            'stats' => [
                ['label' => 'Awards', 'value' => $awards->count()],
                ['label' => 'Best rating', 'value' => $playerOfMatch['value_label'] ?? '-'],
                ['label' => 'Top save', 'value' => $saveOfGame['value_label'] ?? ($turningPoint['value_label'] ?? '-')],
            ],
            'awards' => $awards->all(),
        ];
    }

    private function playerOfTheMatchAward(array $state, Collection $playerDirectory, Collection $clubs): ?array
    {
        $finalStats = collect($state['final_stats'] ?? []);
        $liveStates = collect($state['player_states'] ?? []);

        $candidate = $finalStats->isNotEmpty()
            ? $finalStats
                ->map(function (array $stat): array {
                    $score = ((float) ($stat['rating'] ?? 0) * 10)
                        + ((int) ($stat['goals'] ?? 0) * 14)
                        + ((int) ($stat['assists'] ?? 0) * 8)
                        + ((int) ($stat['shots'] ?? 0) * 1.5);

                    return [...$stat, 'award_score' => $score];
                })
                ->sortByDesc('award_score')
                ->first()
            : $liveStates
                ->map(function (array $stat): array {
                    $score = ((int) ($stat['goals'] ?? 0) * 16)
                        + ((int) ($stat['assists'] ?? 0) * 10)
                        + ((int) ($stat['shots_on_target'] ?? 0) * 3)
                        + ((int) ($stat['shots'] ?? 0) * 1.5)
                        + ((int) ($stat['saves'] ?? 0) * 4)
                        + ((int) ($stat['tackle_won'] ?? 0) * 0.6)
                        + ((int) ($stat['pass_completions'] ?? 0) * 0.05)
                        - ((int) ($stat['yellow_cards'] ?? 0) * 3)
                        - ((int) ($stat['red_cards'] ?? 0) * 10);

                    return [...$stat, 'award_score' => $score];
                })
                ->sortByDesc('award_score')
                ->first();

        if (!$candidate || empty($candidate['player_id'])) {
            return null;
        }

        $player = $playerDirectory->get((int) $candidate['player_id'], []);
        $club = $clubs->get((int) ($candidate['club_id'] ?? $player['club_id'] ?? 0), []);
        $rating = $candidate['rating'] ?? null;
        $goalText = (int) ($candidate['goals'] ?? 0) > 0 ? (int) $candidate['goals'].' goals' : 'strong all-around output';

        return [
            'award_key' => 'player_of_the_match',
            'label' => 'Player of the Match',
            'value_label' => $rating ? number_format((float) $rating, 1) : $goalText,
            'summary' => $rating
                ? 'Led the match with the best overall rating and decisive contributions.'
                : 'Stood out through '.$goalText.' and a strong live stat profile.',
            'player_id' => (int) $candidate['player_id'],
            'player_name' => $player['name'] ?? ($candidate['player_name'] ?? 'Unknown'),
            'photo_url' => $player['photo_url'] ?? null,
            'club_name' => $club['name'] ?? null,
            'club_logo_url' => $club['logo_url'] ?? null,
        ];
    }

    private function turningPointAward(array $state, Collection $playerDirectory, Collection $clubs, GameMatch $match): ?array
    {
        $actions = collect($state['actions'] ?? [])
            ->sortBy(fn (array $action): array => [
                (int) ($action['minute'] ?? 0),
                (int) ($action['second'] ?? 0),
                (int) ($action['sequence'] ?? 0),
            ])
            ->values();

        if ($actions->isEmpty()) {
            return null;
        }

        $home = 0;
        $away = 0;

        $candidate = $actions
            ->map(function (array $action) use (&$home, &$away, $match): ?array {
                $type = (string) ($action['action_type'] ?? '');
                $importance = null;
                $summary = null;
                $minute = min((int) ($action['minute'] ?? 0), 95);
                $beforeHome = $home;
                $beforeAway = $away;
                $beforeDiff = abs($beforeHome - $beforeAway);

                if (in_array($type, ['goal', 'own_goal'], true)) {
                    $scoringClubId = (int) ($action['club_id'] ?? 0);

                    if ($type === 'goal') {
                        if ($scoringClubId === (int) $match->home_club_id) {
                            $home++;
                        } else {
                            $away++;
                        }
                    } else {
                        if ($scoringClubId === (int) $match->home_club_id) {
                            $away++;
                        } else {
                            $home++;
                        }
                    }

                    $afterDiff = abs($home - $away);
                    $wasDraw = $beforeHome === $beforeAway;
                    $leadChanged = ($beforeHome > $beforeAway && $home <= $away) || ($beforeAway > $beforeHome && $away <= $home);
                    $equalizer = !$wasDraw && $home === $away;
                    $decisiveLateGoal = $minute >= 70 && $afterDiff === 1;

                    $importance = 90
                        + ($wasDraw ? 42 : 0)
                        + ($equalizer ? 28 : 0)
                        + ($leadChanged ? 22 : 0)
                        + ($decisiveLateGoal ? 18 : 0)
                        + (($afterDiff > $beforeDiff) ? 10 : 0)
                        + $minute;

                    $summary = match (true) {
                        $wasDraw => 'This goal broke the deadlock and gave one side control of the match.',
                        $equalizer => 'This goal pulled the game level again and completely changed the momentum.',
                        $leadChanged => 'This moment flipped the scoreline and changed who was in command.',
                        $decisiveLateGoal => 'A late goal created the defining swing of the match.',
                        default => 'This action sharply changed the state of the scoreline.',
                    };
                } elseif (in_array($type, ['red_card', 'yellow_red_card'], true)) {
                    $importance = 78
                        + ($beforeDiff <= 1 ? 16 : 0)
                        + ($minute >= 60 ? 14 : 0)
                        + $minute;
                    $summary = $beforeDiff <= 1
                        ? 'A sending off changed a still-close match at a key moment.'
                        : 'A sending off reshaped the tactical balance of the game.';
                } elseif ($type === 'penalty') {
                    $importance = 70
                        + ($beforeDiff <= 1 ? 16 : 0)
                        + ($minute >= 70 ? 12 : 0)
                        + $minute;
                    $summary = 'A penalty situation created one of the defining swings of the match.';
                }

                if ($importance === null) {
                    return null;
                }

                return [...$action, 'importance' => $importance, 'award_summary' => $summary];
            })
            ->filter()
            ->sortByDesc('importance')
            ->first();

        if (!$candidate) {
            return null;
        }

        $playerId = (int) ($candidate['player_id'] ?? $candidate['opponent_player_id'] ?? 0);
        $player = $playerDirectory->get($playerId, []);
        $club = $clubs->get((int) ($candidate['club_id'] ?? $player['club_id'] ?? 0), []);

        return [
            'award_key' => 'turning_point',
            'label' => 'Turning Point',
            'value_label' => trim((string) (($candidate['display_minute'] ?? $candidate['minute'] ?? 0)."'")),
            'summary' => (string) ($candidate['award_summary'] ?? 'A defining moment changed the direction of this fixture.'),
            'player_id' => $playerId > 0 ? $playerId : null,
            'player_name' => $player['name'] ?? ($candidate['player_name'] ?? $candidate['opponent_player_name'] ?? ($club['short_name'] ?? 'Match Event')),
            'photo_url' => $player['photo_url'] ?? ($candidate['player_photo_url'] ?? $candidate['opponent_player_photo_url'] ?? null),
            'club_name' => $club['name'] ?? null,
            'club_logo_url' => $club['logo_url'] ?? ($candidate['club_logo_url'] ?? null),
        ];
    }

    private function saveOfTheGameAward(array $state, Collection $playerDirectory, Collection $clubs, GameMatch $match): ?array
    {
        $timeline = collect($state['actions'] ?? [])
            ->sortBy(fn (array $action): array => [
                (int) ($action['minute'] ?? 0),
                (int) ($action['second'] ?? 0),
                (int) ($action['sequence'] ?? 0),
            ])
            ->values();

        $homeScore = 0;
        $awayScore = 0;

        $saveAction = $timeline
            ->map(function (array $action) use (&$homeScore, &$awayScore, $match): ?array {
                $type = (string) ($action['action_type'] ?? '');
                $minute = min((int) ($action['minute'] ?? 0), 95);
                $beforeHome = $homeScore;
                $beforeAway = $awayScore;

                if ($type === 'goal') {
                    if ((int) ($action['club_id'] ?? 0) === (int) $match->home_club_id) {
                        $homeScore++;
                    } else {
                        $awayScore++;
                    }
                } elseif ($type === 'own_goal') {
                    if ((int) ($action['club_id'] ?? 0) === (int) $match->home_club_id) {
                        $awayScore++;
                    } else {
                        $homeScore++;
                    }
                }

                if ($type !== 'save') {
                    return null;
                }

                $xg = (float) ($action['metadata']['xg'] ?? 0);
                $keeperClubId = (int) ($action['club_id'] ?? 0);
                $isTightGame = abs($beforeHome - $beforeAway) <= 1;
                $protectingLead = ($keeperClubId === (int) $match->home_club_id && $beforeHome > $beforeAway)
                    || ($keeperClubId === (int) $match->away_club_id && $beforeAway > $beforeHome);
                $keepingDrawAlive = $beforeHome === $beforeAway;

                $score = ($xg * 150)
                    + ($minute >= 75 ? 18 : 0)
                    + ($isTightGame ? 18 : 0)
                    + ($protectingLead ? 16 : 0)
                    + ($keepingDrawAlive ? 12 : 0)
                    + $minute;

                return [
                    ...$action,
                    'award_score' => $score,
                    'award_summary' => match (true) {
                        $xg >= 0.30 && $protectingLead => 'A high-value stop protected the lead at a crucial stage.',
                        $xg >= 0.30 && $keepingDrawAlive => 'A high-value save kept the match level in a decisive moment.',
                        $xg >= 0.25 => 'A high-danger save denied one of the best chances of the match.',
                        $protectingLead => 'A timely save protected a narrow advantage.',
                        default => 'A standout save preserved the team during a dangerous moment.',
                    },
                ];
            })
            ->filter()
            ->sortByDesc('award_score')
            ->first();

        if ($saveAction) {
            $playerId = (int) ($saveAction['player_id'] ?? 0);
            $player = $playerDirectory->get($playerId, []);
            $club = $clubs->get((int) ($saveAction['club_id'] ?? $player['club_id'] ?? 0), []);

            return [
                'award_key' => 'save_of_the_game',
                'label' => 'Save of the Game',
                'value_label' => $saveAction['metadata']['xg']
                    ? 'xG '.number_format((float) $saveAction['metadata']['xg'], 2)
                    : trim((string) (($saveAction['display_minute'] ?? $saveAction['minute'] ?? 0)."'")),
                'summary' => (string) ($saveAction['award_summary'] ?? 'A standout save preserved the team during a dangerous moment.'),
                'player_id' => $playerId ?: null,
                'player_name' => $player['name'] ?? ($saveAction['player_name'] ?? 'Goalkeeper'),
                'photo_url' => $player['photo_url'] ?? ($saveAction['player_photo_url'] ?? null),
                'club_name' => $club['name'] ?? null,
                'club_logo_url' => $club['logo_url'] ?? ($saveAction['club_logo_url'] ?? null),
            ];
        }

        $goalkeeper = collect($state['player_states'] ?? [])
            ->filter(fn (array $player): bool => (int) ($player['saves'] ?? 0) > 0)
            ->sortByDesc('saves')
            ->first();

        if (!$goalkeeper) {
            return null;
        }

        $player = $playerDirectory->get((int) $goalkeeper['player_id'], []);
        $club = $clubs->get((int) ($goalkeeper['club_id'] ?? $player['club_id'] ?? 0), []);

        return [
            'award_key' => 'save_of_the_game',
            'label' => 'Save of the Game',
            'value_label' => (int) $goalkeeper['saves'].' saves',
            'summary' => 'Finished as the top shot-stopper in the match when no standout xG save was available.',
            'player_id' => (int) $goalkeeper['player_id'],
            'player_name' => $player['name'] ?? ($goalkeeper['player_name'] ?? 'Goalkeeper'),
            'photo_url' => $player['photo_url'] ?? null,
            'club_name' => $club['name'] ?? null,
            'club_logo_url' => $club['logo_url'] ?? null,
        ];
    }
}
