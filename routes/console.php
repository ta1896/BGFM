<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Str;
use App\Models\CompetitionSeason;
use App\Models\SimulationSchedulerRun;
use App\Jobs\SimulateScheduledMatchesJob;
use App\Services\LiveMatchTickerService;
use App\Services\PlayerClubBackfillService;
use App\Services\SeasonProgressionService;
use App\Services\SimulationSettingsService;
use App\Services\StatisticsAggregationService;
use Database\Seeders\TestFactorySeeder;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('game:process-matchday {--competition-season=}', function (SeasonProgressionService $service) {
    $competitionSeasonId = $this->option('competition-season');
    $competitionSeason = null;

    if ($competitionSeasonId !== null) {
        $competitionSeason = CompetitionSeason::find((int) $competitionSeasonId);
        if (!$competitionSeason) {
            $this->error('CompetitionSeason nicht gefunden: ' . $competitionSeasonId);

            return 1;
        }
    }

    $summary = $service->processNextMatchday($competitionSeason);

    $this->info('Spieltag-Prozess abgeschlossen.');
    $this->table(
        ['Wert', 'Anzahl'],
        [
            ['Verarbeitete Wettbewerbe', $summary['processed_competitions']],
            ['Simulierte Spiele', $summary['matches_simulated']],
            ['Finanz-Abrechnungen', $summary['match_settlements']],
            ['Finalisierte Saisons', $summary['seasons_finalized']],
            ['Aufstiege', $summary['promotions']],
            ['Abstiege', $summary['relegations']],
            ['Stadionprojekte abgeschlossen', $summary['stadium_projects_completed']],
            ['Trainingslager aktiviert', $summary['training_camps_activated']],
            ['Trainingslager abgeschlossen', $summary['training_camps_completed']],
            ['Sponsorvertraege ausgelaufen', $summary['sponsor_contracts_expired']],
            ['Beendete Leihen', $summary['loans_completed']],
            ['Team of the Day erzeugt', $summary['team_of_the_day_generated']],
            ['Random Events erzeugt', $summary['random_events_generated']],
            ['Random Events angewendet', $summary['random_events_applied']],
        ]
    );

    return 0;
})->purpose('Verarbeitet den naechsten offenen Spieltag und finalisiert Saisons automatisch');

Schedule::command('game:process-matchday')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

Artisan::command('game:simulate-matches {--limit=} {--types=} {--minutes-per-run=} {--force} {--ids=}', function (SimulationSettingsService $simulationSettings, LiveMatchTickerService $tickerService) {
    $truncateMessage = static function (?string $message, int $maxBytes = 250): ?string {
        if ($message === null) {
            return null;
        }

        $message = trim($message);
        if ($message === '') {
            return null;
        }

        return strlen($message) <= $maxBytes ? $message : substr($message, 0, $maxBytes);
    };

    $simulationSettings->applyRuntimeOverrides();

    $force = (bool) $this->option('force');
    $limitOption = $this->option('limit');
    $limit = $limitOption === null ? $simulationSettings->schedulerDefaultLimit() : max(0, (int) $limitOption);

    $minutesPerRunOption = $this->option('minutes-per-run');
    $minutesPerRun = $minutesPerRunOption === null
        ? $simulationSettings->schedulerDefaultMinutesPerRun()
        : max(1, min(90, (int) $minutesPerRunOption));

    $allowedTypes = ['friendly', 'league', 'cup'];
    $typesOption = $this->option('types');
    $defaultTypes = $simulationSettings->schedulerDefaultTypes();
    $inputTypes = array_filter(array_map(
        static fn(string $value): string => trim(strtolower($value)),
        explode(',', $typesOption === null ? implode(',', $defaultTypes) : (string) $typesOption)
    ));
    $types = array_values(array_intersect($allowedTypes, $inputTypes));
    if ($types === []) {
        $types = $defaultTypes !== [] ? $defaultTypes : $allowedTypes;
    }

    $runnerLockSeconds = max(30, (int) config('simulation.scheduler.runner_lock_seconds', 120));
    $runnerLock = Cache::lock('simulation:scheduler:runner', $runnerLockSeconds);
    $runToken = (string) Str::uuid();
    $runningStaleAfterSeconds = max(60, (int) config('simulation.scheduler.health.running_stale_after_seconds', 600));
    $abandonedRunsRecovered = 0;

    try {
        $abandonedRunsRecovered = SimulationSchedulerRun::query()
            ->where('status', 'running')
            ->whereNotNull('started_at')
            ->where('started_at', '<=', now()->subSeconds($runningStaleAfterSeconds))
            ->update([
                'status' => 'abandoned',
                'finished_at' => now(),
                'message' => 'Als abgebrochener Lauf markiert (Timeout).',
                'updated_at' => now(),
            ]);
    } catch (Throwable) {
        $abandonedRunsRecovered = 0;
    }

    $run = null;
    try {
        $run = SimulationSchedulerRun::query()->create([
            'run_token' => $runToken,
            'status' => 'running',
            'trigger' => $force ? 'forced' : 'scheduled',
            'forced' => $force,
            'requested_limit' => $limit,
            'requested_minutes_per_run' => $minutesPerRun,
            'requested_types' => $types,
            'runner_lock_seconds' => $runnerLockSeconds,
            'started_at' => now(),
            'message' => $truncateMessage($abandonedRunsRecovered > 0 ? ('Recovered stale runs: ' . $abandonedRunsRecovered) : null),
        ]);
    } catch (Throwable) {
        $run = null;
    }

    $updateRun = static function (?SimulationSchedulerRun $run, array $attributes) use ($truncateMessage): void {
        if (!$run) {
            return;
        }

        if (array_key_exists('message', $attributes)) {
            $attributes['message'] = $truncateMessage(
                is_scalar($attributes['message']) || $attributes['message'] === null
                ? (string) ($attributes['message'] ?? '')
                : null
            );
        }

        $run->fill($attributes);
        $run->save();
    };

    if (!$runnerLock->get()) {
        $updateRun($run, [
            'status' => 'skipped_locked',
            'finished_at' => now(),
            'message' => 'Runner lock aktiv, Lauf ausgelassen.',
        ]);

        $this->info('Auto-Simulation uebersprungen (ein anderer Runner ist noch aktiv).');

        return 0;
    }

    try {
        if (!$force && !$simulationSettings->isScheduledSimulationDue()) {
            $intervalMinutes = max(1, (int) config('simulation.scheduler.interval_minutes', 1));
            $lastRunAt = $simulationSettings->scheduledSimulationLastRunAt();
            $skipMessage = 'Intervall ' . $intervalMinutes . ' Minute(n), letzter Lauf: '
                . ($lastRunAt ? $lastRunAt->format('Y-m-d H:i:s') : 'n/a') . '.';

            $updateRun($run, [
                'status' => 'skipped_interval',
                'finished_at' => now(),
                'message' => $skipMessage,
            ]);

            $this->info(
                'Auto-Simulation uebersprungen (' . $skipMessage . ')'
            );

            return 0;
        }

        $idsOption = $this->option('ids');
        $matchIds = $idsOption ? array_map('intval', explode(',', (string) $idsOption)) : [];

        $job = new SimulateScheduledMatchesJob($limit, $types, $minutesPerRun, $matchIds);
        $summary = $job->handle($tickerService);
        $simulationSettings->markScheduledSimulationRun();
        $hasFailures = (int) ($summary['failed_matches'] ?? 0) > 0;
        $runStatus = $hasFailures ? 'completed_with_errors' : 'completed';

        $updateRun($run, [
            'status' => $runStatus,
            'candidate_matches' => (int) ($summary['candidate_matches'] ?? 0),
            'claimed_matches' => (int) ($summary['claimed_matches'] ?? 0),
            'processed_matches' => (int) ($summary['processed_matches'] ?? 0),
            'failed_matches' => (int) ($summary['failed_matches'] ?? 0),
            'skipped_active_claims' => (int) ($summary['skipped_active_claims'] ?? 0),
            'skipped_unclaimable' => (int) ($summary['skipped_unclaimable'] ?? 0),
            'stale_claim_takeovers' => (int) ($summary['stale_claim_takeovers'] ?? 0),
            'finished_at' => now(),
            'message' => $abandonedRunsRecovered > 0 ? ('Recovered stale runs: ' . $abandonedRunsRecovered) : null,
        ]);

        $this->info(
            'Auto-Simulation abgeschlossen (limit: ' . $limit . ', minutes-per-run: ' . $minutesPerRun . ', types: ' . implode(',', $types) . ', processed: '
            . (int) ($summary['processed_matches'] ?? 0) . ', failures: ' . (int) ($summary['failed_matches'] ?? 0) . ').'
        );

        return 0;
    } catch (Throwable $exception) {
        $updateRun($run, [
            'status' => 'failed',
            'finished_at' => now(),
            'message' => $truncateMessage($exception->getMessage()),
        ]);

        throw $exception;
    } finally {
        $runnerLock->release();
    }
})->purpose('Startet und tickt alle offenen Freundschafts-, Liga- und Pokalspiele automatisch (Live-Simulation)');

Schedule::command('game:simulate-matches')
    ->everyMinute()
    ->withoutOverlapping();

Artisan::command('game:simulation-health {--limit=20} {--strict}', function () {
    $limit = max(5, min(200, (int) $this->option('limit')));

    try {
        $runs = SimulationSchedulerRun::query()
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    } catch (Throwable) {
        $this->warn('Keine Scheduler-Laufdaten verfuegbar (Tabelle fehlt oder DB nicht bereit).');

        return 0;
    }

    if ($runs->isEmpty()) {
        $this->warn('Keine Scheduler-Laufdaten vorhanden.');

        return 0;
    }

    $this->info('Simulation Health (letzte ' . $runs->count() . ' Laeufe)');
    $this->table(
        ['Run', 'Status', 'Processed', 'Failed', 'Stale-Takeovers', 'Started', 'Finished'],
        $runs->map(fn(SimulationSchedulerRun $run): array => [
            (string) $run->run_token,
            (string) $run->status,
            (int) $run->processed_matches,
            (int) $run->failed_matches,
            (int) $run->stale_claim_takeovers,
            optional($run->started_at)->format('Y-m-d H:i:s') ?? '-',
            optional($run->finished_at)->format('Y-m-d H:i:s') ?? '-',
        ])->all()
    );

    $failedRuns = $runs->whereIn('status', ['failed', 'completed_with_errors'])->count();
    $skippedLockedRuns = $runs->where('status', 'skipped_locked')->count();
    $staleTakeovers = (int) $runs->sum('stale_claim_takeovers');
    $abandonedRuns = $runs->where('status', 'abandoned')->count();

    $failedThreshold = max(0, (int) config('simulation.scheduler.health.failed_runs_alert_threshold', 0));
    $skippedLockedThreshold = max(0, (int) config('simulation.scheduler.health.skipped_locked_alert_threshold', 1));
    $staleTakeoversThreshold = max(0, (int) config('simulation.scheduler.health.stale_takeovers_alert_threshold', 0));
    $abandonedRunsThreshold = max(0, (int) config('simulation.scheduler.health.abandoned_runs_alert_threshold', 0));

    $alerts = [];
    if ($failedRuns > $failedThreshold) {
        $alerts[] = 'failed_runs=' . $failedRuns . ' (threshold ' . $failedThreshold . ')';
    }
    if ($skippedLockedRuns > $skippedLockedThreshold) {
        $alerts[] = 'skipped_locked=' . $skippedLockedRuns . ' (threshold ' . $skippedLockedThreshold . ')';
    }
    if ($staleTakeovers > $staleTakeoversThreshold) {
        $alerts[] = 'stale_takeovers=' . $staleTakeovers . ' (threshold ' . $staleTakeoversThreshold . ')';
    }
    if ($abandonedRuns > $abandonedRunsThreshold) {
        $alerts[] = 'abandoned_runs=' . $abandonedRuns . ' (threshold ' . $abandonedRunsThreshold . ')';
    }

    if ($alerts === []) {
        $this->info('Status ok: keine Fehler-/Lock-Haeufung in den letzten Laeufen.');

        return 0;
    }

    $this->warn('Warnung: ' . implode(', ', $alerts));

    if ((bool) $this->option('strict')) {
        $this->error('Strict mode: Alert-Schwelle ueberschritten.');

        return 1;
    }

    return 0;
})->purpose('Zeigt Betriebskennzahlen der letzten Simulationslaeufe inkl. Warnindikatoren.');

Artisan::command('game:simulation-health-check {--limit=} {--strict}', function () {
    $defaultLimit = max(5, min(200, (int) config('simulation.scheduler.health.check_limit', 60)));
    $limitOption = $this->option('limit');
    $limit = $limitOption === null ? $defaultLimit : max(5, min(200, (int) $limitOption));

    $strict = (bool) config('simulation.scheduler.health.check_strict', true) || (bool) $this->option('strict');
    $parameters = ['--limit' => $limit];
    if ($strict) {
        $parameters['--strict'] = true;
    }

    $exitCode = Artisan::call('game:simulation-health', $parameters);
    $output = trim((string) Artisan::output());

    if ($exitCode !== 0) {
        Log::error('Simulation health check failed.', [
            'exit_code' => $exitCode,
            'limit' => $limit,
            'strict' => $strict,
            'output' => $output,
        ]);

        if ($output !== '') {
            $this->line($output);
        }

        return $exitCode;
    }

    if ((bool) config('simulation.scheduler.health.log_success', false)) {
        Log::info('Simulation health check passed.', [
            'limit' => $limit,
            'strict' => $strict,
            'output' => $output,
        ]);
    }

    if ($output !== '') {
        $this->line($output);
    }

    return 0;
})->purpose('Fuehrt den Simulations-Healthcheck aus und schreibt Alerts bei Schwellwert-Ueberschreitung ins Log.');

if ((bool) config('simulation.scheduler.health.check_enabled', true)) {
    Schedule::command('game:simulation-health-check')
        ->everyFiveMinutes()
        ->withoutOverlapping();
}

Artisan::command(
    'game:seed-test-factory {--leagues=2} {--clubs-per-league=10} {--players-per-club=20} {--seed-year=2026}',
    function () {
        $leagues = max(1, (int) $this->option('leagues'));
        $clubsPerLeague = max(2, (int) $this->option('clubs-per-league'));
        $playersPerClub = max(11, (int) $this->option('players-per-club'));
        $seedYear = max(2020, (int) $this->option('seed-year'));

        config([
            'test_factory.leagues' => $leagues,
            'test_factory.clubs_per_league' => $clubsPerLeague,
            'test_factory.players_per_club' => $playersPerClub,
            'test_factory.seed_year' => $seedYear,
        ]);

        $this->call('db:seed', ['--class' => TestFactorySeeder::class, '--force' => true]);

        $matchesPerLeague = $clubsPerLeague * ($clubsPerLeague - 1);
        $totalMatches = $matchesPerLeague * $leagues;
        $totalClubs = $clubsPerLeague * $leagues;
        $totalPlayers = $totalClubs * $playersPerClub;

        $this->info('TestFactory erfolgreich erstellt.');
        $this->table(
            ['Bereich', 'Wert'],
            [
                ['Ligen', $leagues],
                ['Vereine', $totalClubs],
                ['Spieler', $totalPlayers],
                ['Liga-Spiele (scheduled)', $totalMatches],
                ['Manager Login', 'test.manager@openws.local / password'],
                ['Admin Login', 'test.admin@openws.local / password'],
            ]
        );

        return 0;
    }
)->purpose('Erzeugt reproduzierbare Testdaten (Ligen, Vereine, Spieler, Spielplan, Admin/Manager).');

Artisan::command('game:backfill-player-club-model {--dry-run} {--chunk=500}', function (PlayerClubBackfillService $service) {
    $dryRun = (bool) $this->option('dry-run');
    $chunk = max(50, min(5000, (int) $this->option('chunk')));

    $report = $service->run($dryRun, $chunk);

    $this->info('Backfill fuer Spieler/Verein abgeschlossen' . ($dryRun ? ' (Dry-Run)' : '') . '.');

    $this->table(
        ['Kennzahl', 'Wert'],
        [
            ['Dry-Run', $report['dry_run'] ? 'ja' : 'nein'],
            ['Chunk-Groesse', $report['chunk_size']],
            ['Start', $report['started_at']],
            ['Ende', $report['finished_at']],
            ['Spieler gescannt', $report['players_scanned']],
            ['Spieler aktualisiert', $report['players_updated']],
            ['position_main gefuellt', $report['players_position_main_filled']],
            ['Positionsprofil normalisiert', $report['players_profile_normalized']],
            ['Kontext-Sperren initialisiert', $report['players_context_suspensions_seeded']],
            ['Legacy-Sperren synchronisiert', $report['players_legacy_suspension_synced']],
            ['Spieler-Status repariert', $report['players_status_repaired']],
            ['Vereine gescannt', $report['clubs_scanned']],
            ['Vereine aktualisiert', $report['clubs_updated']],
            ['Slug gefuellt', $report['clubs_slug_filled']],
            ['Short-Name gefuellt', $report['clubs_short_name_filled']],
        ]
    );

    $this->newLine();
    $this->info('Audit vor Backfill:');
    $this->table(
        ['Pruefung', 'Anzahl'],
        collect($report['audit_before'])->map(fn($value, $key) => [$key, $value])->values()->all()
    );

    $this->newLine();
    $this->info($dryRun ? 'Audit nach Backfill (simuliert):' : 'Audit nach Backfill:');
    $this->table(
        ['Pruefung', 'Anzahl'],
        collect($report['audit_after'])->map(fn($value, $key) => [$key, $value])->values()->all()
    );

    return 0;
})->purpose('Backfill und Integritaets-Audit fuer Spieler-/Vereinsfelder (Positionen, Sperren, Slug/Short-Name).');

Artisan::command(
    'game:rebuild-statistics {--all} {--competition-season=} {--match=} {--audit}',
    function (StatisticsAggregationService $service) {
        $all = (bool) $this->option('all');
        $competitionSeasonId = $this->option('competition-season');
        $matchId = $this->option('match');

        if (!$all && $competitionSeasonId === null && $matchId === null) {
            $all = true;
        }

        $rebuiltLeagueSeasons = 0;
        $rebuiltPlayerMatches = 0;
        $rebuiltPlayerGlobal = false;

        if ($matchId !== null) {
            $match = \App\Models\GameMatch::query()->find((int) $matchId);
            if (!$match) {
                $this->error('Match nicht gefunden: ' . $matchId);

                return 1;
            }

            $service->rebuildPlayerCompetitionStatsForMatch($match);
            $rebuiltPlayerMatches++;

            if ($match->competition_season_id) {
                $competitionSeason = \App\Models\CompetitionSeason::query()->find((int) $match->competition_season_id);
                if ($competitionSeason) {
                    $service->rebuildLeagueTable($competitionSeason);
                    $rebuiltLeagueSeasons++;
                }
            }
        }

        if ($competitionSeasonId !== null) {
            $competitionSeason = \App\Models\CompetitionSeason::query()->find((int) $competitionSeasonId);
            if (!$competitionSeason) {
                $this->error('CompetitionSeason nicht gefunden: ' . $competitionSeasonId);

                return 1;
            }

            $service->rebuildLeagueTable($competitionSeason);
            $rebuiltLeagueSeasons++;
        }

        if ($all) {
            $seasons = \App\Models\CompetitionSeason::query()->get();
            foreach ($seasons as $season) {
                $service->rebuildLeagueTable($season);
            }
            $rebuiltLeagueSeasons += $seasons->count();

            $service->rebuildAllPlayerCompetitionStats();
            $rebuiltPlayerGlobal = true;
        }

        $this->info('Statistik-Rebuild abgeschlossen.');
        $this->table(
            ['Kennzahl', 'Wert'],
            [
                ['League-Seasons rebuilt', $rebuiltLeagueSeasons],
                ['Player-Stats rebuilt (Match)', $rebuiltPlayerMatches],
                ['Player-Stats rebuilt (Global)', $rebuiltPlayerGlobal ? 'ja' : 'nein'],
                ['Modus --all', $all ? 'ja' : 'nein'],
            ]
        );

        if ((bool) $this->option('audit')) {
            $audit = $service->auditStatisticsIntegrity(
                $competitionSeasonId !== null ? (int) $competitionSeasonId : null
            );

            $this->newLine();
            $this->info('Integritaets-Audit:');
            $this->table(
                ['Pruefung', 'Anzahl'],
                collect($audit)->map(fn($value, $key) => [$key, $value])->values()->all()
            );
        }

        return 0;
    }
)->purpose('Rebuild + optionales Integritaets-Audit fuer League-/Player-Statistiken aus Match-Rohdaten.');
