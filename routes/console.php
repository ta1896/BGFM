<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\CompetitionSeason;
use App\Jobs\SimulateScheduledMatchesJob;
use App\Services\SeasonProgressionService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('game:process-matchday {--competition-season=}', function (SeasonProgressionService $service) {
    $competitionSeasonId = $this->option('competition-season');
    $competitionSeason = null;

    if ($competitionSeasonId !== null) {
        $competitionSeason = CompetitionSeason::find((int) $competitionSeasonId);
        if (!$competitionSeason) {
            $this->error('CompetitionSeason nicht gefunden: '.$competitionSeasonId);

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

Artisan::command('game:simulate-matches {--limit=0} {--types=friendly,league,cup} {--minutes-per-run=5}', function () {
    $limit = max(0, (int) $this->option('limit'));
    $minutesPerRun = max(1, min(90, (int) $this->option('minutes-per-run') ?: 5));
    $allowedTypes = ['friendly', 'league', 'cup'];
    $inputTypes = array_filter(array_map(
        static fn (string $value): string => trim(strtolower($value)),
        explode(',', (string) $this->option('types'))
    ));
    $types = array_values(array_intersect($allowedTypes, $inputTypes));
    if ($types === []) {
        $types = $allowedTypes;
    }

    SimulateScheduledMatchesJob::dispatchSync($limit, $types, $minutesPerRun);

    $this->info(
        'Auto-Simulation abgeschlossen (limit: '.$limit.', minutes-per-run: '.$minutesPerRun.', types: '.implode(',', $types).').'
    );

    return 0;
})->purpose('Startet und tickt alle offenen Freundschafts-, Liga- und Pokalspiele automatisch (Live-Simulation)');

Schedule::command('game:simulate-matches --limit=0 --minutes-per-run=5')
    ->everyMinute()
    ->withoutOverlapping();
