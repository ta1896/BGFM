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
                'live-center-match-shotmap' => $this->shotMapPanelData($match, $state),
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
                    $score = ((float) ($stat['rating'] ?? 0) * 12)
                        + ((int) ($stat['goals'] ?? 0) * 18)
                        + ((int) ($stat['assists'] ?? 0) * 10)
                        + ((int) ($stat['shots'] ?? 0) * 1.5);

                    return [...$stat, 'award_score' => $score];
                })
                ->sortByDesc('award_score')
                ->first()
            : $liveStates
                ->map(function (array $stat): array {
                    $score = ((int) ($stat['goals'] ?? 0) * 20)
                        + ((int) ($stat['assists'] ?? 0) * 12)
                        + ((int) ($stat['shots_on_target'] ?? 0) * 3)
                        + ((int) ($stat['shots'] ?? 0) * 1.5)
                        + ((int) ($stat['saves'] ?? 0) * 5)
                        + ((int) ($stat['tackle_won'] ?? 0) * 0.8)
                        + ((int) ($stat['pass_completions'] ?? 0) * 0.05)
                        - ((int) ($stat['yellow_cards'] ?? 0) * 3)
                        - ((int) ($stat['red_cards'] ?? 0) * 12);

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
        
        $goals = (int) ($candidate['goals'] ?? 0);
        $assists = (int) ($candidate['assists'] ?? 0);
        $saves = (int) ($candidate['saves'] ?? 0);

        $summary = "Herausragende Leistung: ";
        if ($goals > 0) $summary .= "Mit {$goals} " . ($goals > 1 ? 'Toren' : 'Tor') . " ";
        if ($assists > 0) $summary .= ($goals > 0 ? 'und ' : 'Mit ') . "{$assists} " . ($assists > 1 ? 'Vorlagen ' : 'Vorlage ');
        if ($saves > 3) $summary .= "Sowie {$saves} Glanzparaden ";
        
        $summary .= $rating 
            ? "dominierte er die Partie (Rating: " . number_format((float) $rating, 1) . ")." 
            : "war er der Dreh- und Angelpunkt des Spiels.";

        return [
            'award_key' => 'player_of_the_match',
            'label' => 'Spieler des Spiels',
            'value_label' => $rating ? number_format((float) $rating, 1) : ($goals . ' ' . ($goals === 1 ? 'Tor' : 'Tore')),
            'summary' => $summary,
            'player_id' => (int) $candidate['player_id'],
            'player_name' => $player['name'] ?? ($candidate['player_name'] ?? 'Unbekannt'),
            'photo_url' => $player['photo_url'] ?? null,
            'club_name' => $club['name'] ?? null,
            'club_logo_url' => $club['logo_url'] ?? null,
            'meta' => [
                'rating' => $rating,
                'goals' => $goals,
                'assists' => $assists,
                'saves' => $saves,
            ]
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

                    $afterHome = $home;
                    $afterAway = $away;
                    $afterDiff = abs($afterHome - $afterAway);
                    
                    $wasDraw = $beforeHome === $beforeAway;
                    $isDraw = $afterHome === $afterAway;
                    $leadChanged = ($beforeHome > $beforeAway && $afterAway > $afterHome) || ($beforeAway > $beforeHome && $afterHome > $afterAway);
                    $equalizer = !$wasDraw && $isDraw;
                    $decisiveLateGoal = $minute >= 80 && $afterDiff === 1;
                    $winningGoal = $wasDraw && $minute >= 85 && $afterDiff === 1;

                    $importance = 100
                        + ($wasDraw ? 50 : 0)
                        + ($equalizer ? 40 : 0)
                        + ($leadChanged ? 60 : 0)
                        + ($decisiveLateGoal ? 30 : 0)
                        + ($winningGoal ? 80 : 0)
                        + $minute;

                    $summary = match (true) {
                        $winningGoal => "DER SIEGTREFFER! In der Nachspielzeit erzielt, entschied dieser Moment die komplette Partie.",
                        $leadChanged => "Die totale Wende: Dieses Tor hat das Spiel komplett auf den Kopf gestellt.",
                        $equalizer => "Der Momentum-Killer: Der Ausgleich zum psychologisch wichtigsten Zeitpunkt.",
                        $wasDraw => "Der Dosenöffner: Nach langem Warten brach dieser Treffer endlich den Bann.",
                        $decisiveLateGoal => "Vorentscheidung: Ein später Treffer, der den Widerstand des Gegners brach.",
                        default => "Spielentscheidende Szene, die den weiteren Verlauf massiv prägte.",
                    };
                } elseif (in_array($type, ['red_card', 'yellow_red_card'], true)) {
                    $importance = 85
                        + ($beforeDiff <= 1 ? 25 : 0)
                        + ($minute >= 60 ? 15 : 0)
                        + $minute;
                    $summary = "Platzverweis-Drama: Diese Karte zwang das Team zur taktischen Neuausrichtung.";
                } elseif ($type === 'penalty') {
                    $importance = 75
                        + ($beforeDiff <= 1 ? 20 : 0)
                        + $minute;
                    $summary = "Elfmeter-Krimi: Ein Moment höchster Anspannung, der die Weichen neu stellte.";
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
            'label' => 'Wendepunkt',
            'value_label' => trim((string) (($candidate['display_minute'] ?? $candidate['minute'] ?? 0)."'")),
            'summary' => (string) ($candidate['award_summary'] ?? 'Ein definierender Moment änderte die Richtung dieses Spiels.'),
            'player_id' => $playerId > 0 ? $playerId : null,
            'player_name' => $player['name'] ?? ($candidate['player_name'] ?? $candidate['opponent_player_name'] ?? ($club['short_name'] ?? 'Spielereignis')),
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

                $score = ($xg * 200)
                    + ($minute >= 75 ? 25 : 0)
                    + ($isTightGame ? 20 : 0)
                    + ($protectingLead ? 20 : 0)
                    + ($keepingDrawAlive ? 15 : 0)
                    + $minute;

                return [
                    ...$action,
                    'award_score' => $score,
                    'award_summary' => match (true) {
                        $xg >= 0.40 => "UNHALTBAR? Nicht für ihn! Eine absolute Monster-Parade gegen einen Schuss aus kürzester Distanz.",
                        $xg >= 0.25 && $protectingLead => "Sieg festgehalten: Mit diesem Reflex rettete er die knappe Führung kurz vor dem Ende.",
                        $keepingDrawAlive && $minute >= 80 => "Punktteiler gesichert: Diese Parade verhinderte die drohende Niederlage in der Schlussphase.",
                        $xg >= 0.20 => "Großtat: Er fischte den Ball sensationell aus dem Eck und rettete sein Team.",
                        default => "Starker Reflex: In einer brenzligen Situation war er zur Stelle und bewahrte die Ruhe.",
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
                'label' => 'Parade des Spiels',
                'value_label' => $saveAction['metadata']['xg']
                    ? 'xG '.number_format((float) $saveAction['metadata']['xg'], 2)
                    : trim((string) (($saveAction['display_minute'] ?? $saveAction['minute'] ?? 0)."'")),
                'summary' => (string) ($saveAction['award_summary'] ?? 'Eine Glanzparade verhinderte den Einschlag in einer kritischen Phase.'),
                'player_id' => $playerId ?: null,
                'player_name' => $player['name'] ?? ($saveAction['player_name'] ?? 'Torhüter'),
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
            'label' => 'Parade des Spiels',
            'value_label' => (int) $goalkeeper['saves'].' Paraden',
            'summary' => 'Er war heute der Fels in der Brandung und entschärfte insgesamt ' . (int) $goalkeeper['saves'] . ' Schüsse.',
            'player_id' => (int) $goalkeeper['player_id'],
            'player_name' => $player['name'] ?? ($goalkeeper['player_name'] ?? 'Torhüter'),
            'photo_url' => $player['photo_url'] ?? null,
            'club_name' => $club['name'] ?? null,
            'club_logo_url' => $club['logo_url'] ?? null,
        ];
    }

    private function shotMapPanelData(GameMatch $match, array $state): array
    {
        $actions = collect($state['actions'] ?? []);
        $shots = $actions->filter(function (array $action): bool {
            return in_array($action['action_type'], ['goal', 'penalty', 'shot_on_target', 'shot_off_target', 'shot_blocked', 'shot'], true);
        })->values();

        $homeShots = $shots->where('club_id', $match->home_club_id);
        $awayShots = $shots->where('club_id', $match->away_club_id);

        $formatShots = function (Collection $teamShots): array {
            return $teamShots->map(function (array $s): array {
                $meta = is_string($s['metadata'] ?? null) ? json_decode($s['metadata'], true) : ($s['metadata'] ?? []);

                return [
                    'id' => $s['id'] ?? null,
                    'type' => $s['action_type'],
                    'minute' => $s['minute'],
                    'player_name' => $s['player_name'] ?? 'Spieler',
                    'x' => (float) ($meta['x'] ?? ($s['x_coord'] ?? 50)),
                    'y' => (float) ($meta['y'] ?? ($s['y_coord'] ?? 50)),
                    'xg' => (float) ($meta['xg'] ?? 0.05),
                    'is_goal' => $s['action_type'] === 'goal' || $s['action_type'] === 'penalty',
                ];
            })->all();
        };

        $homeAvgXg = $homeShots->isEmpty() ? 0 : round($homeShots->avg(function ($s) {
            $meta = is_string($s['metadata'] ?? null) ? json_decode($s['metadata'], true) : ($s['metadata'] ?? []);
            return (float) ($meta['xg'] ?? 0.05);
        }), 2);
        $awayAvgXg = $awayShots->isEmpty() ? 0 : round($awayShots->avg(function ($s) {
            $meta = is_string($s['metadata'] ?? null) ? json_decode($s['metadata'], true) : ($s['metadata'] ?? []);
            return (float) ($meta['xg'] ?? 0.05);
        }), 2);

        return [
            'headline' => 'Visual Shot Map',
            'summary' => 'Spatial distribution and quality (xG) of all attempts on goal.',
            'stats' => [
                ['label' => 'Total Shots', 'value' => $shots->count()],
                ['label' => 'Avg. xG (H)', 'value' => $homeAvgXg],
                ['label' => 'Avg. xG (A)', 'value' => $awayAvgXg],
            ],
            'home_shots' => $formatShots($homeShots),
            'away_shots' => $formatShots($awayShots),
            'home_logo' => $match->homeClub->logo_url,
            'away_logo' => $match->awayClub->logo_url,
        ];
    }
}
