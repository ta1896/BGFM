<?php

namespace App\Services;

use App\Models\GameMatch;

class MatchCenterStateService
{
    public function build(
        GameMatch $match,
        LeagueTableService $leagueTableService,
        array $lineups,
        bool $canSimulate,
        array $manageableClubIds,
    ): array {
        $statusLabel = match ($match->status) {
            'played' => 'Beendet',
            'live' => $match->live_paused ? 'Pausiert' : 'Live',
            default => ucfirst((string) $match->status),
        };

        return [
            'id' => $match->id,
            'status' => $match->status,
            'status_label' => $statusLabel,
            'live_minute' => (int) $match->live_minute,
            'display_minute' => $this->displayMinute((int) $match->live_minute),
            'live_paused' => (bool) $match->live_paused,
            'live_error_message' => $match->live_error_message,
            'home_score' => $match->home_score,
            'away_score' => $match->away_score,
            'can_simulate' => $canSimulate,
            'manageable_club_ids' => $manageableClubIds,
            'lineups' => $lineups,
            'events' => $match->events
                ->sortByDesc(fn ($event) => ($event->minute * 60) + $event->second)
                ->values()
                ->map(function ($event): array {
                    return [
                        'id' => $event->id,
                        'minute' => (int) $event->minute,
                        'display_minute' => $this->displayMinute((int) $event->minute, is_array($event->metadata) ? $event->metadata : []),
                        'second' => (int) $event->second,
                        'event_type' => (string) $event->event_type,
                        'club_id' => $event->club_id !== null ? (int) $event->club_id : null,
                        'player_id' => $event->player_id !== null ? (int) $event->player_id : null,
                        'player_name' => $event->player?->full_name,
                        'assister_name' => $event->assister?->full_name,
                        'club_short_name' => $event->club?->short_name ?: $event->club?->name,
                        'narrative' => (string) ($event->narrative ?? ''),
                        'metadata' => is_array($event->metadata) ? $event->metadata : [],
                    ];
                })
                ->all(),
            'team_states' => $match->liveTeamStates
                ->mapWithKeys(function ($state): array {
                    return [
                        (string) $state->club_id => [
                            'club_id' => (int) $state->club_id,
                            'tactical_style' => (string) $state->tactical_style,
                            'phase' => (string) ($state->phase ?? ''),
                            'possession_seconds' => (int) $state->possession_seconds,
                            'actions_count' => (int) $state->actions_count,
                            'dangerous_attacks' => (int) $state->dangerous_attacks,
                            'pass_attempts' => (int) $state->pass_attempts,
                            'pass_completions' => (int) $state->pass_completions,
                            'tackle_attempts' => (int) $state->tackle_attempts,
                            'tackle_won' => (int) $state->tackle_won,
                            'fouls_committed' => (int) $state->fouls_committed,
                            'corners_won' => (int) $state->corners_won,
                            'shots' => (int) $state->shots,
                            'shots_on_target' => (int) $state->shots_on_target,
                            'expected_goals' => (float) $state->expected_goals,
                            'yellow_cards' => (int) $state->yellow_cards,
                            'red_cards' => (int) $state->red_cards,
                            'substitutions_used' => (int) $state->substitutions_used,
                            'tactical_changes_count' => (int) $state->tactical_changes_count,
                            'last_tactical_change_minute' => $state->last_tactical_change_minute !== null ? (int) $state->last_tactical_change_minute : null,
                            'last_substitution_minute' => $state->last_substitution_minute !== null ? (int) $state->last_substitution_minute : null,
                        ],
                    ];
                })
                ->all(),
            'player_states' => $match->livePlayerStates
                ->map(function ($state): array {
                    return [
                        'player_id' => (int) $state->player_id,
                        'club_id' => (int) $state->club_id,
                        'player_name' => $state->player?->full_name,
                        'slot' => (string) ($state->slot ?? ''),
                        'is_on_pitch' => (bool) $state->is_on_pitch,
                        'is_sent_off' => (bool) $state->is_sent_off,
                        'is_injured' => (bool) $state->is_injured,
                        'fit_factor' => (float) $state->fit_factor,
                        'minutes_played' => (int) $state->minutes_played,
                        'ball_contacts' => (int) $state->ball_contacts,
                        'pass_attempts' => (int) $state->pass_attempts,
                        'pass_completions' => (int) $state->pass_completions,
                        'tackle_attempts' => (int) $state->tackle_attempts,
                        'tackle_won' => (int) $state->tackle_won,
                        'fouls_committed' => (int) $state->fouls_committed,
                        'fouls_suffered' => (int) $state->fouls_suffered,
                        'shots' => (int) $state->shots,
                        'shots_on_target' => (int) $state->shots_on_target,
                        'goals' => (int) $state->goals,
                        'assists' => (int) $state->assists,
                        'yellow_cards' => (int) $state->yellow_cards,
                        'red_cards' => (int) $state->red_cards,
                        'saves' => (int) $state->saves,
                        'photo_url' => $state->player?->photo_url,
                    ];
                })
                ->values()
                ->all(),
            'final_stats' => $match->playerStats
                ->map(function ($stat): array {
                    return [
                        'player_id' => (int) $stat->player_id,
                        'club_id' => (int) $stat->club_id,
                        'player_name' => $stat->player?->full_name,
                        'rating' => (float) $stat->rating,
                        'goals' => (int) $stat->goals,
                        'assists' => (int) $stat->assists,
                        'minutes_played' => (int) $stat->minutes_played,
                        'shots' => (int) $stat->shots,
                    ];
                })
                ->values()
                ->all(),
            'actions' => ($match->liveActions->isNotEmpty() ? $match->liveActions : $match->events)
                ->sortByDesc(fn ($item) => ($item->minute * 100000) + ($item->second * 1000) + ($item->sequence ?? 0))
                ->take(400)
                ->values()
                ->map(function ($item): array {
                    $isAction = isset($item->action_type);
                    $metadata = is_array($item->metadata) ? $item->metadata : [];
                    $assisterName = $isAction ? ($metadata['assister_name'] ?? null) : $item->assister?->full_name;

                    return [
                        'id' => (int) $item->id,
                        'minute' => (int) $item->minute,
                        'display_minute' => $this->displayMinute((int) $item->minute, $metadata),
                        'second' => (int) $item->second,
                        'sequence' => (int) ($item->sequence ?? 0),
                        'club_id' => $item->club_id !== null ? (int) $item->club_id : null,
                        'club_short_name' => $item->club?->short_name ?: $item->club?->name,
                        'club_logo_url' => $item->club?->logo_url,
                        'player_id' => $item->player_id !== null ? (int) $item->player_id : null,
                        'player_name' => $item->player?->full_name,
                        'player_photo_url' => $item->player?->photo_url,
                        'assister_name' => $assisterName,
                        'opponent_player_id' => $isAction && $item->opponent_player_id !== null ? (int) $item->opponent_player_id : null,
                        'opponent_player_name' => $isAction ? $item->opponentPlayer?->full_name : null,
                        'opponent_player_photo_url' => $isAction ? $item->opponentPlayer?->photo_url : null,
                        'action_type' => (string) ($isAction ? $item->action_type : $item->event_type),
                        'outcome' => (string) ($item->outcome ?? ''),
                        'narrative' => (string) ($item->narrative ?? ''),
                        'x_coord' => $isAction ? (float) $item->x_coord : null,
                        'y_coord' => $isAction ? (float) $item->y_coord : null,
                        'metadata' => $metadata,
                    ];
                })
                ->all(),
            'planned_substitutions' => $match->plannedSubstitutions
                ->map(function ($plan): array {
                    return [
                        'id' => (int) $plan->id,
                        'club_id' => (int) $plan->club_id,
                        'player_out_id' => $plan->player_out_id !== null ? (int) $plan->player_out_id : null,
                        'player_out_name' => $plan->playerOut?->full_name,
                        'player_in_id' => $plan->player_in_id !== null ? (int) $plan->player_in_id : null,
                        'player_in_name' => $plan->playerIn?->full_name,
                        'planned_minute' => (int) $plan->planned_minute,
                        'score_condition' => (string) $plan->score_condition,
                        'target_slot' => (string) ($plan->target_slot ?? ''),
                        'status' => (string) $plan->status,
                        'executed_minute' => $plan->executed_minute !== null ? (int) $plan->executed_minute : null,
                        'metadata' => $plan->metadata,
                    ];
                })
                ->values()
                ->all(),
            'live_table' => $this->liveTablePayload($match, $leagueTableService),
            'minute_snapshots' => $match->liveMinuteSnapshots
                ->sortByDesc('minute')
                ->take(30)
                ->values()
                ->map(function ($snapshot): array {
                    return [
                        'minute' => (int) $snapshot->minute,
                        'home_score' => (int) $snapshot->home_score,
                        'away_score' => (int) $snapshot->away_score,
                        'home_phase' => (string) ($snapshot->home_phase ?? ''),
                        'away_phase' => (string) ($snapshot->away_phase ?? ''),
                        'home_tactical_style' => (string) ($snapshot->home_tactical_style ?? ''),
                        'away_tactical_style' => (string) ($snapshot->away_tactical_style ?? ''),
                        'pending_plans' => (int) $snapshot->pending_plans,
                        'executed_plans' => (int) $snapshot->executed_plans,
                        'skipped_plans' => (int) $snapshot->skipped_plans,
                        'invalid_plans' => (int) $snapshot->invalid_plans,
                    ];
                })
                ->all(),
        ];
    }

    private function liveTablePayload(GameMatch $match, LeagueTableService $leagueTableService): ?array
    {
        $competitionSeason = $match->competitionSeason;

        if (!$competitionSeason || $match->type !== 'league') {
            return null;
        }

        $rows = $leagueTableService->table($competitionSeason)
            ->map(function ($row): array {
                return [
                    'club_id' => (int) $row->club_id,
                    'club_name' => (string) ($row->club?->name ?? ''),
                    'club_short_name' => (string) ($row->club?->short_name ?? $row->club?->name ?? ''),
                    'club_logo_url' => $row->club?->logo_url,
                    'played' => (int) ($row->matches_played ?? 0),
                    'won' => (int) ($row->wins ?? 0),
                    'drawn' => (int) ($row->draws ?? 0),
                    'lost' => (int) ($row->losses ?? 0),
                    'goals_for' => (int) ($row->goals_for ?? 0),
                    'goals_against' => (int) ($row->goals_against ?? 0),
                    'goal_diff' => (int) ($row->goal_diff ?? 0),
                    'points' => (int) ($row->points ?? 0),
                    'form' => collect($row->form_last5 ?? [])->values()->all(),
                ];
            })
            ->keyBy('club_id');

        if ($match->status === 'live') {
            $homeId = (int) $match->home_club_id;
            $awayId = (int) $match->away_club_id;
            $homeScore = (int) ($match->home_score ?? 0);
            $awayScore = (int) ($match->away_score ?? 0);

            if ($rows->has($homeId) && $rows->has($awayId)) {
                $home = $rows[$homeId];
                $away = $rows[$awayId];

                $home['played'] += 1;
                $away['played'] += 1;
                $home['goals_for'] += $homeScore;
                $home['goals_against'] += $awayScore;
                $away['goals_for'] += $awayScore;
                $away['goals_against'] += $homeScore;

                if ($homeScore > $awayScore) {
                    $home['won'] += 1;
                    $home['points'] += 3;
                    $away['lost'] += 1;
                    array_unshift($home['form'], 'W');
                    array_unshift($away['form'], 'L');
                } elseif ($homeScore < $awayScore) {
                    $away['won'] += 1;
                    $away['points'] += 3;
                    $home['lost'] += 1;
                    array_unshift($home['form'], 'L');
                    array_unshift($away['form'], 'W');
                } else {
                    $home['drawn'] += 1;
                    $away['drawn'] += 1;
                    $home['points'] += 1;
                    $away['points'] += 1;
                    array_unshift($home['form'], 'D');
                    array_unshift($away['form'], 'D');
                }

                $home['goal_diff'] = $home['goals_for'] - $home['goals_against'];
                $away['goal_diff'] = $away['goals_for'] - $away['goals_against'];
                $home['form'] = array_slice($home['form'], 0, 5);
                $away['form'] = array_slice($away['form'], 0, 5);

                $rows[$homeId] = $home;
                $rows[$awayId] = $away;
            }
        }

        return [
            'competition' => (string) ($competitionSeason->competition?->name ?? 'Liga'),
            'rows' => $rows->values()
                ->sort(fn (array $a, array $b): int => [$b['points'], $b['goal_diff'], $b['goals_for']] <=> [$a['points'], $a['goal_diff'], $a['goals_for']])
                ->values()
                ->map(fn (array $row, int $index): array => array_merge($row, ['position' => $index + 1]))
                ->all(),
            'home_club_id' => (int) $match->home_club_id,
            'away_club_id' => (int) $match->away_club_id,
            'is_live_projection' => $match->status === 'live',
        ];
    }

    private function displayMinute(int $minute, array $metadata = []): string
    {
        $explicit = $metadata['display_minute'] ?? null;
        if (is_string($explicit) && trim($explicit) !== '') {
            return trim($explicit);
        }

        if (is_numeric($explicit)) {
            return (string) (int) $explicit;
        }

        $stoppageBase = $metadata['stoppage_base'] ?? null;
        $stoppageMinutes = $metadata['stoppage_minutes'] ?? null;

        if (is_numeric($stoppageBase) && is_numeric($stoppageMinutes)) {
            return (int) $stoppageBase . '+' . (int) $stoppageMinutes;
        }

        return (string) max(0, $minute);
    }
}
