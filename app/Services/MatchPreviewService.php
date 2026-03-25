<?php

namespace App\Services;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Lineup;
use App\Models\Player;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class MatchPreviewService
{
    public function __construct(
        private readonly FormationPlannerService $formationPlanner,
        private readonly PlayerPositionService $positionService,
    ) {
    }

    public function comparison(GameMatch $match): array
    {
        return [
            'home' => $this->comparisonMetrics($match->homeClub),
            'away' => $this->comparisonMetrics($match->awayClub),
        ];
    }

    public function preMatchReport(GameMatch $match, LeagueTableService $leagueTableService, array $comparison): array
    {
        $keyPlayers = $this->keyPlayers($match);

        return [
            'recent_form' => [
                'home' => $this->recentForm($match->home_club_id, $match->id),
                'away' => $this->recentForm($match->away_club_id, $match->id),
            ],
            'league_snapshot' => $this->leagueSnapshot($match, $leagueTableService),
            'head_to_head' => $this->headToHead($match),
            'insights' => $this->insightBullets($match, $comparison),
            'key_players' => $keyPlayers,
            'absentees' => $this->absentees($match),
            'key_duels' => $this->keyDuels($match, $keyPlayers),
            'expected_lineup_preview' => $this->expectedLineupPreview($match),
        ];
    }

    public function lineupsPayload(GameMatch $match): array
    {
        $clubIds = [(int) $match->home_club_id, (int) $match->away_club_id];
        $lineups = [];

        foreach ($clubIds as $clubId) {
            /** @var Lineup|null $lineup */
            $lineup = Lineup::query()
                ->with(['players:id,first_name,last_name,position,position_main,position_second,position_third,overall,photo_path'])
                ->where('match_id', $match->id)
                ->where('club_id', $clubId)
                ->first();

            $players = collect();
            $formation = $this->formationPlanner->defaultFormation();

            if (!$lineup) {
                $club = $clubId === (int) $match->home_club_id ? $match->homeClub : $match->awayClub;
                $selection = $this->formationPlanner->strongestByFormation(
                    $club->players()->whereIn('status', ['active', 'transfer_listed'])->get(),
                    $this->formationPlanner->defaultFormation(),
                    5
                );

                $formation = $this->formationPlanner->defaultFormation();
                $players = $this->draftPlayersFromSelection($selection);
            } else {
                $formation = (string) $lineup->formation;
                $players = $lineup->players;
            }

            $slotLayouts = collect($this->formationPlanner->starterSlots($formation))
                ->keyBy(fn (array $slot): string => strtoupper((string) ($slot['slot'] ?? '')));

            $mappedPlayers = $players->map(function ($player) use ($slotLayouts): array {
                $slot = (string) ($player->pivot->pitch_position ?? '');
                $isRemoved = str_starts_with(strtoupper($slot), 'OUT-');
                $fitFactor = $this->positionService->fitFactorWithProfile(
                    (string) ($player->position_main ?: $player->position),
                    (string) $player->position_second,
                    (string) $player->position_third,
                    $slot
                );

                $payload = [
                    'id' => (int) $player->id,
                    'name' => $player->full_name,
                    'position' => (string) ($player->position_main ?: $player->position),
                    'slot' => $slot,
                    'sort_order' => (int) $player->pivot->sort_order,
                    'is_bench' => (bool) $player->pivot->is_bench,
                    'is_removed' => $isRemoved,
                    'bench_order' => $player->pivot->bench_order !== null ? (int) $player->pivot->bench_order : null,
                    'fit_factor' => round($fitFactor, 2),
                    'overall' => (int) $player->overall,
                    'photo_url' => $player->photo_url,
                    'is_captain' => (bool) $player->pivot->is_captain,
                    'instructions' => $this->normalizeInstructions($player->pivot->instructions ?? null),
                ];

                $layout = $slotLayouts->get(strtoupper($slot));

                if (!$layout || $payload['is_bench'] || $payload['is_removed']) {
                    return $payload;
                }

                $payload['pitch_x'] = isset($layout['x']) ? (int) $layout['x'] : null;
                $payload['pitch_y'] = isset($layout['y']) ? (int) $layout['y'] : null;
                $payload['slot_group'] = (string) ($layout['group'] ?? '');

                return $payload;
            });

            $lineups[(string) $clubId] = [
                'club_id' => $clubId,
                'formation' => $formation,
                'tactical_style' => $lineup ? (string) $lineup->tactical_style : 'balanced',
                'mentality' => $lineup ? (string) ($lineup->mentality ?? 'normal') : 'normal',
                'aggression' => $lineup ? (string) ($lineup->aggression ?? 'normal') : 'normal',
                'line_height' => $lineup ? (string) ($lineup->line_height ?? 'normal') : 'normal',
                'attack_focus' => $lineup ? (string) $lineup->attack_focus : 'center',
                'offside_trap' => $lineup ? (bool) $lineup->offside_trap : false,
                'time_wasting' => $lineup ? (bool) $lineup->time_wasting : false,
                'pressing_intensity' => $lineup ? (string) ($lineup->pressing_intensity ?? 'normal') : 'normal',
                'line_of_engagement' => $lineup ? (string) ($lineup->line_of_engagement ?? 'normal') : 'normal',
                'pressing_trap' => $lineup ? (string) ($lineup->pressing_trap ?? 'none') : 'none',
                'cross_engagement' => $lineup ? (string) ($lineup->cross_engagement ?? 'none') : 'none',
                'corner_marking_strategy' => $lineup ? (string) ($lineup->corner_marking_strategy ?? 'zonal') : 'zonal',
                'free_kick_marking_strategy' => $lineup ? (string) ($lineup->free_kick_marking_strategy ?? 'zonal') : 'zonal',
                'captain_player_id' => $lineup ? (int) ($lineup->players->firstWhere('pivot.is_captain', true)?->id ?? 0) : 0,
                'set_pieces' => [
                    'penalty_taker_player_id' => $lineup ? (int) ($lineup->penalty_taker_player_id ?? 0) : 0,
                    'free_kick_near_player_id' => $lineup ? (int) ($lineup->free_kick_near_player_id ?? 0) : 0,
                    'free_kick_far_player_id' => $lineup ? (int) ($lineup->free_kick_far_player_id ?? 0) : 0,
                    'corner_left_taker_player_id' => $lineup ? (int) ($lineup->corner_left_taker_player_id ?? 0) : 0,
                    'corner_right_taker_player_id' => $lineup ? (int) ($lineup->corner_right_taker_player_id ?? 0) : 0,
                ],
                'starters' => $mappedPlayers
                    ->where('is_bench', false)
                    ->where('is_removed', false)
                    ->sortBy('sort_order')
                    ->values()
                    ->all(),
                'bench' => $mappedPlayers
                    ->where('is_bench', true)
                    ->where('is_removed', false)
                    ->sortBy('bench_order')
                    ->values()
                    ->all(),
                'removed' => $mappedPlayers
                    ->where('is_removed', true)
                    ->sortBy('sort_order')
                    ->values()
                    ->all(),
            ];
        }

        return $lineups;
    }

    /**
     * @return array<int, string>
     */
    private function normalizeInstructions(mixed $instructions): array
    {
        if (is_string($instructions)) {
            $decoded = json_decode($instructions, true);
            return is_array($decoded) ? array_values(array_map('strval', $decoded)) : [];
        }

        if (is_array($instructions)) {
            return array_values(array_map('strval', $instructions));
        }

        return [];
    }

    private function comparisonMetrics(Club $club): array
    {
        $scoreColumns = $this->availablePlayerScoreColumns();
        $selects = ['market_value', 'age', 'overall'];

        foreach (['morale', 'happiness', 'stamina', 'sharpness', 'fatigue'] as $column) {
            if (in_array($column, $scoreColumns, true)) {
                $selects[] = $column;
            }
        }

        $players = $club->players()
            ->where('status', 'active')
            ->get($selects);

        $corePlayers = $players->sortByDesc('overall')->take(14)->values();
        $fitnessMetric = $this->resolveFitnessMetric($corePlayers, $scoreColumns);
        $moraleMetric = in_array('morale', $scoreColumns, true)
            ? $corePlayers->avg('morale')
            : $corePlayers->avg('happiness');

        return [
            'market_value' => (float) ($players->sum('market_value') ?? 0),
            'avg_age' => round((float) ($players->avg('age') ?? 0), 1),
            'strength' => round((float) ($corePlayers->avg('overall') ?? 0), 1),
            'morale' => round((float) ($moraleMetric ?? 0), 1),
            'fitness' => round($fitnessMetric, 1),
            'debug' => [
                'core_player_count' => $corePlayers->count(),
                'morale_source' => $this->resolveMoraleMetricLabel($scoreColumns),
                'fitness_source' => $this->resolveFitnessMetricLabel($scoreColumns),
                'strength_top_players' => $this->topPlayerPayloads($corePlayers, 'overall'),
                'market_value_top_players' => $this->topPlayerPayloads($players, 'market_value'),
                'fitness_value' => round($fitnessMetric, 1),
                'morale_value' => round((float) ($moraleMetric ?? 0), 1),
            ],
        ];
    }

    private function availablePlayerScoreColumns(): array
    {
        static $columns = null;

        if ($columns !== null) {
            return $columns;
        }

        return $columns = Schema::getColumnListing('players');
    }

    private function resolveFitnessMetric(Collection $players, array $scoreColumns): float
    {
        if (in_array('stamina', $scoreColumns, true)) {
            return (float) ($players->avg('stamina') ?? 0);
        }

        if (in_array('sharpness', $scoreColumns, true) && in_array('fatigue', $scoreColumns, true)) {
            return (float) ($players->avg(
                fn ($player) => max(0, min(100, (((int) ($player->sharpness ?? 0)) + (100 - (int) ($player->fatigue ?? 0))) / 2))
            ) ?? 0);
        }

        if (in_array('sharpness', $scoreColumns, true)) {
            return (float) ($players->avg('sharpness') ?? 0);
        }

        if (in_array('fatigue', $scoreColumns, true)) {
            return (float) ($players->avg(fn ($player) => max(0, 100 - (int) ($player->fatigue ?? 0))) ?? 0);
        }

        return 0.0;
    }

    private function resolveMoraleMetricLabel(array $scoreColumns): string
    {
        return in_array('morale', $scoreColumns, true) ? 'Morale' : 'Happiness';
    }

    private function resolveFitnessMetricLabel(array $scoreColumns): string
    {
        if (in_array('stamina', $scoreColumns, true)) {
            return 'Stamina';
        }

        if (in_array('sharpness', $scoreColumns, true) && in_array('fatigue', $scoreColumns, true)) {
            return 'Sharpness/Fatigue Mix';
        }

        if (in_array('sharpness', $scoreColumns, true)) {
            return 'Sharpness';
        }

        if (in_array('fatigue', $scoreColumns, true)) {
            return 'Fatigue Inverse';
        }

        return 'No Fitness Data';
    }

    private function topPlayerPayloads(Collection $players, string $metric): array
    {
        return $players
            ->sortByDesc(fn ($player) => (float) ($player->{$metric} ?? 0))
            ->take(3)
            ->map(fn ($player) => [
                'name' => $player->full_name,
                'position' => (string) ($player->position_main ?: $player->position ?: '-'),
                'value' => round((float) ($player->{$metric} ?? 0), $metric === 'market_value' ? 0 : 1),
            ])
            ->values()
            ->all();
    }

    private function recentForm(int $clubId, int $currentMatchId): array
    {
        $matches = GameMatch::query()
            ->where('status', 'played')
            ->where('id', '!=', $currentMatchId)
            ->where(function (Builder $query) use ($clubId) {
                $query->where('home_club_id', $clubId)
                    ->orWhere('away_club_id', $clubId);
            })
            ->with(['homeClub:id,name,short_name,logo_path', 'awayClub:id,name,short_name,logo_path'])
            ->orderByDesc('kickoff_at')
            ->take(5)
            ->get();

        $form = $matches->map(function (GameMatch $pastMatch) use ($clubId): array {
            $isHome = (int) $pastMatch->home_club_id === $clubId;
            $goalsFor = (int) ($isHome ? $pastMatch->home_score : $pastMatch->away_score);
            $goalsAgainst = (int) ($isHome ? $pastMatch->away_score : $pastMatch->home_score);
            $opponent = $isHome ? $pastMatch->awayClub : $pastMatch->homeClub;
            $result = $goalsFor > $goalsAgainst ? 'W' : ($goalsFor < $goalsAgainst ? 'L' : 'D');

            return [
                'id' => $pastMatch->id,
                'result' => $result,
                'score' => $goalsFor . ':' . $goalsAgainst,
                'opponent_name' => $opponent?->short_name ?: $opponent?->name ?: 'Gegner',
                'opponent_logo_url' => $opponent?->logo_url,
                'is_home' => $isHome,
                'kickoff_label' => $pastMatch->kickoff_at?->format('d.m.'),
                'relative_label' => $pastMatch->kickoff_at
                    ? sprintf('vor %d Tagen', now()->diffInDays($pastMatch->kickoff_at))
                    : null,
                'competition_name' => $pastMatch->competitionSeason?->competition?->name
                    ?? ($pastMatch->type === 'league' ? 'Liga' : ($pastMatch->type === 'friendly' ? 'Testspiel' : 'Pokal')),
                'trend_rating' => round($this->formTrendRating($goalsFor, $goalsAgainst), 2),
            ];
        })->values();

        return [
            'matches' => $form->all(),
            'wins' => $form->where('result', 'W')->count(),
            'draws' => $form->where('result', 'D')->count(),
            'losses' => $form->where('result', 'L')->count(),
            'points' => ($form->where('result', 'W')->count() * 3) + $form->where('result', 'D')->count(),
        ];
    }

    private function formTrendRating(int $goalsFor, int $goalsAgainst): float
    {
        $goalDiff = $goalsFor - $goalsAgainst;
        $base = match (true) {
            $goalDiff >= 2 => 8.8,
            $goalDiff === 1 => 8.1,
            $goalDiff === 0 => 7.1,
            $goalDiff === -1 => 6.4,
            default => 5.8,
        };

        $bonus = min(0.5, max(-0.3, ($goalsFor * 0.12) - ($goalsAgainst * 0.08)));

        return max(5.0, min(9.8, $base + $bonus));
    }

    private function leagueSnapshot(GameMatch $match, LeagueTableService $leagueTableService): ?array
    {
        if ($match->type !== 'league' || !$match->competitionSeason) {
            return null;
        }

        $table = $leagueTableService->table($match->competitionSeason)->values();
        $homeRow = $table->firstWhere('club_id', $match->home_club_id);
        $awayRow = $table->firstWhere('club_id', $match->away_club_id);

        if (!$homeRow && !$awayRow) {
            return null;
        }

        return [
            'competition' => $match->competitionSeason->competition?->name,
            'home' => $homeRow ? [
                'position' => (int) ($homeRow->position ?? 0),
                'points' => (int) ($homeRow->points ?? 0),
                'goal_diff' => (int) ($homeRow->goal_diff ?? 0),
            ] : null,
            'away' => $awayRow ? [
                'position' => (int) ($awayRow->position ?? 0),
                'points' => (int) ($awayRow->points ?? 0),
                'goal_diff' => (int) ($awayRow->goal_diff ?? 0),
            ] : null,
        ];
    }

    private function headToHead(GameMatch $match): array
    {
        $matches = GameMatch::query()
            ->where('status', 'played')
            ->where(function (Builder $query) use ($match): void {
                $query->where(function (Builder $inner) use ($match): void {
                    $inner->where('home_club_id', $match->home_club_id)
                        ->where('away_club_id', $match->away_club_id);
                })->orWhere(function (Builder $inner) use ($match): void {
                    $inner->where('home_club_id', $match->away_club_id)
                        ->where('away_club_id', $match->home_club_id);
                });
            })
            ->orderByDesc('kickoff_at')
            ->take(5)
            ->get();

        $entries = $matches->map(function (GameMatch $entry) use ($match): array {
            $homeGoals = (int) ($entry->home_score ?? 0);
            $awayGoals = (int) ($entry->away_score ?? 0);
            $homePerspectiveGoals = (int) ((int) $entry->home_club_id === (int) $match->home_club_id ? $homeGoals : $awayGoals);
            $awayPerspectiveGoals = (int) ((int) $entry->home_club_id === (int) $match->home_club_id ? $awayGoals : $homeGoals);
            $winner = $homePerspectiveGoals > $awayPerspectiveGoals ? 'home' : ($homePerspectiveGoals < $awayPerspectiveGoals ? 'away' : 'draw');

            return [
                'id' => $entry->id,
                'date' => $entry->kickoff_at?->format('d.m.Y'),
                'score' => $homePerspectiveGoals . ':' . $awayPerspectiveGoals,
                'winner' => $winner,
            ];
        })->values();

        return [
            'matches' => $entries->all(),
            'home_wins' => $entries->where('winner', 'home')->count(),
            'draws' => $entries->where('winner', 'draw')->count(),
            'away_wins' => $entries->where('winner', 'away')->count(),
        ];
    }

    private function insightBullets(GameMatch $match, array $comparison): array
    {
        $homeStrength = (float) ($comparison['home']['strength'] ?? 0);
        $awayStrength = (float) ($comparison['away']['strength'] ?? 0);
        $homeMarket = (float) ($comparison['home']['market_value'] ?? 0);
        $awayMarket = (float) ($comparison['away']['market_value'] ?? 0);
        $homeFitness = (float) ($comparison['home']['fitness'] ?? 0);
        $awayFitness = (float) ($comparison['away']['fitness'] ?? 0);

        $strengthLeader = $homeStrength >= $awayStrength
            ? ($match->homeClub?->short_name ?: $match->homeClub?->name ?: 'Heimteam')
            : ($match->awayClub?->short_name ?: $match->awayClub?->name ?: 'Auswaertsteam');
        $marketLeader = $homeMarket >= $awayMarket
            ? ($match->homeClub?->short_name ?: $match->homeClub?->name ?: 'Heimteam')
            : ($match->awayClub?->short_name ?: $match->awayClub?->name ?: 'Auswaertsteam');
        $fitnessLeader = $homeFitness >= $awayFitness
            ? ($match->homeClub?->short_name ?: $match->homeClub?->name ?: 'Heimteam')
            : ($match->awayClub?->short_name ?: $match->awayClub?->name ?: 'Auswaertsteam');

        return [
            $strengthLeader . ' geht mit einem leichten Vorteil in der Kaderstaerke in dieses Duell.',
            $marketLeader . ' bringt aktuell den hoeheren Gesamtmarktwert auf den Platz.',
            $fitnessLeader . ' wirkt vor dem Anpfiff im Schnitt etwas frischer.',
        ];
    }

    private function availableMatchCenterPlayers(int $clubId, string $suspensionField): Builder
    {
        return Player::query()
            ->where('club_id', $clubId)
            ->where('medical_status', 'fit')
            ->where('status', 'active')
            ->where($suspensionField, 0);
    }

    private function matchCenterPlayerPayload(Player $player, bool $includeStyle = false): array
    {
        $payload = [
            'id' => $player->id,
            'name' => $player->full_name,
            'overall' => (int) $player->overall,
            'photo_url' => $player->photo_url,
            'position' => $player->display_position,
        ];

        if ($includeStyle) {
            $payload['style'] = $player->player_style;
        }

        return $payload;
    }

    private function keyPlayers(GameMatch $match): array
    {
        $suspensionField = $this->getSuspensionField($match->type ?? 'league');

        $fetcher = fn (int $clubId) => $this->availableMatchCenterPlayers($clubId, $suspensionField)
            ->orderByDesc('overall')
            ->take(2)
            ->get()
            ->map(fn (Player $player) => $this->matchCenterPlayerPayload($player, true));

        return [
            'home' => $fetcher($match->home_club_id),
            'away' => $fetcher($match->away_club_id),
        ];
    }

    private function absentees(GameMatch $match): array
    {
        $suspensionField = $this->getSuspensionField($match->type ?? 'league');

        $fetcher = fn (int $clubId) => Player::query()
            ->where('club_id', $clubId)
            ->where(function (Builder $query) use ($suspensionField) {
                $query->where('medical_status', '!=', 'fit')
                    ->orWhere($suspensionField, '>', 0);
            })
            ->with(['injuries' => fn ($query) => $query->where('status', 'active')])
            ->get()
            ->map(fn (Player $player) => [
                'id' => $player->id,
                'name' => $player->full_name,
                'reason' => $player->{$suspensionField} > 0 ? 'Gesperrt' : ($player->injuries->first()?->injury_type ?? 'Verletzt'),
                'type' => $player->{$suspensionField} > 0 ? 'suspension' : 'injury',
            ]);

        return [
            'home' => $fetcher($match->home_club_id),
            'away' => $fetcher($match->away_club_id),
        ];
    }

    private function keyDuels(GameMatch $match, array $keyPlayers): array
    {
        $duels = [];
        $suspensionField = $this->getSuspensionField($match->type ?? 'league');

        $homeP1 = $keyPlayers['home'][0] ?? null;
        $awayP1 = $keyPlayers['away'][0] ?? null;

        if ($homeP1 && $awayP1) {
            $duels[] = [
                'label' => 'Star-Vgl.',
                'home' => $homeP1,
                'away' => $awayP1,
            ];
        }

        $homeAttacker = $this->availableMatchCenterPlayers($match->home_club_id, $suspensionField)
            ->whereIn('position', ['MS', 'ST', 'HS', 'LF', 'RF', 'OM'])
            ->orderByDesc('overall')
            ->first();

        $awayDefender = $this->availableMatchCenterPlayers($match->away_club_id, $suspensionField)
            ->whereIn('position', ['IV', 'CB', 'LB', 'RB', 'LV', 'RV', 'DM'])
            ->orderByDesc('overall')
            ->first();

        if ($homeAttacker instanceof Player && $awayDefender instanceof Player) {
            $duels[] = [
                'label' => 'Angriff vs Abwehr',
                'home' => $this->matchCenterPlayerPayload($homeAttacker),
                'away' => $this->matchCenterPlayerPayload($awayDefender),
            ];
        }

        return $duels;
    }

    private function expectedLineupPreview(GameMatch $match): array
    {
        $payload = $this->lineupsPayload($match);

        return [
            'home' => collect($payload[(string) $match->home_club_id]['starters'] ?? [])
                ->map(fn (array $starter) => [
                    'id' => $starter['id'],
                    'name' => $starter['name'],
                    'position' => $starter['position'],
                    'slot' => $starter['slot'],
                ]),
            'away' => collect($payload[(string) $match->away_club_id]['starters'] ?? [])
                ->map(fn (array $starter) => [
                    'id' => $starter['id'],
                    'name' => $starter['name'],
                    'position' => $starter['position'],
                    'slot' => $starter['slot'],
                ]),
        ];
    }

    private function getSuspensionField(string $type): string
    {
        return match ($type) {
            'league' => 'suspension_league_remaining',
            'cup_national' => 'suspension_cup_national_remaining',
            'cup_international' => 'suspension_cup_international_remaining',
            'friendly' => 'suspension_friendly_remaining',
            default => 'suspension_matches_remaining',
        };
    }

    private function draftPlayersFromSelection(array $selection): Collection
    {
        $allDraftPlayerIds = array_merge(array_values($selection['starters'] ?? []), array_values($selection['bench'] ?? []));
        $allDraftPlayers = Player::query()
            ->whereIn('id', $allDraftPlayerIds)
            ->get(['id', 'first_name', 'last_name', 'position', 'position_main', 'position_second', 'position_third', 'overall', 'photo_path'])
            ->keyBy('id');

        $players = collect();

        foreach ($selection['starters'] ?? [] as $slot => $playerId) {
            $player = $allDraftPlayers->get($playerId);
            if ($player) {
                $players->push($this->draftLineupPlayer($player, $slot, 1, false));
            }
        }

        foreach ($selection['bench'] ?? [] as $index => $playerId) {
            $player = $allDraftPlayers->get($playerId);
            if ($player) {
                $players->push($this->draftLineupPlayer($player, 'BANK-' . ($index + 1), 100 + $index, true, $index + 1));
            }
        }

        return $players;
    }

    private function draftLineupPlayer(Player $player, string $pitchPosition, int $sortOrder, bool $isBench, ?int $benchOrder = null): object
    {
        return (object) [
            'id' => $player->id,
            'full_name' => $player->full_name,
            'position_main' => $player->position_main,
            'position' => $player->position,
            'position_second' => $player->position_second,
            'position_third' => $player->position_third,
            'overall' => $player->overall,
            'photo_url' => $player->photo_url,
            'pivot' => (object) [
                'pitch_position' => $pitchPosition,
                'sort_order' => $sortOrder,
                'is_bench' => $isBench,
                'bench_order' => $benchOrder,
            ],
        ];
    }
}
