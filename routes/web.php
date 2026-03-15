<?php

use App\Http\Controllers\Admin\ClubController as AdminClubController;
use App\Http\Controllers\Admin\CompetitionController as AdminCompetitionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LineupController as AdminLineupController;
use App\Http\Controllers\Admin\PlayerController as AdminPlayerController;
use App\Http\Controllers\Admin\SimulationController as AdminSimulationController;
use App\Http\Controllers\AwardsController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\FreeClubController;
use App\Http\Controllers\FriendlyMatchController;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\LineupsController;
use App\Http\Controllers\ManagerLiveController;
use App\Http\Controllers\MatchCenterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MedicalController;
use App\Http\Controllers\ScoutingController;
use App\Http\Controllers\SponsorController;
use App\Http\Controllers\StadiumController;
use App\Http\Controllers\TeamOfTheDayController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\TrainingCampController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return inertia('Home');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/freie-vereine', [FreeClubController::class, 'index'])->name('clubs.free');
    Route::post('/freie-vereine/{club}/uebernehmen', [FreeClubController::class, 'claim'])->name('clubs.claim');
    Route::resource('clubs', ClubController::class)->only(['create', 'store']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');
    Route::delete('/settings/passkeys/{id}', [App\Http\Controllers\SettingsController::class, 'destroyPasskey'])->name('settings.passkeys.destroy');

    // WebAuthn Routes
    \Laragear\WebAuthn\Http\Routes::routes();

    Route::middleware('has.club.or.admin')->group(
        function () {
            Route::resource('clubs', ClubController::class)->except(['create', 'store']);
            Route::get('/players/hierarchy', [PlayerController::class, 'hierarchy'])->name('squad-hierarchy.index');
            Route::resource('players', PlayerController::class)->only(['index', 'show', 'update']);
            Route::post('/players/{player}/playtime-promise', [PlayerController::class, 'storePlaytimePromise'])->name('players.playtime-promise.store');
            Route::post('/players/{player}/conversation', [PlayerController::class, 'storeConversation'])->name('players.conversations.store');
            Route::post('/players/{player}/hierarchy-role', [PlayerController::class, 'updateHierarchyRole'])->name('players.hierarchy-role.update');
            Route::post('/players/{player}/quick-promise', [PlayerController::class, 'storeQuickPromise'])->name('players.quick-promise.store');
            Route::resource('lineups', LineupsController::class);
            Route::post('/lineups/{lineup}/activate', [LineupsController::class, 'activate'])->name('lineups.activate');
            Route::get('/matches', [LeagueController::class, 'matches'])->name('league.matches');
            Route::get('/matches/{match}', [MatchCenterController::class, 'show'])->name('matches.show');
            Route::match(['get', 'post'], '/matches/{match}/simulate', [MatchCenterController::class, 'simulate'])->name('matches.simulate')->middleware('throttle:heavy_task');
            Route::post('/matches/{match}/live/start', [MatchCenterController::class, 'liveStart'])->name('matches.live.start');
            Route::post('/matches/{match}/live/resume', [MatchCenterController::class, 'liveResume'])->name('matches.live.resume');
            Route::post('/matches/{match}/live/style', [MatchCenterController::class, 'liveSetStyle'])->name('matches.live.style');
            Route::post('/matches/{match}/live/substitute', [MatchCenterController::class, 'liveSubstitute'])->name('matches.live.substitute');
            Route::post('/matches/{match}/live/substitute/plan', [MatchCenterController::class, 'livePlanSubstitute'])->name('matches.live.substitute.plan');
            Route::post('/matches/{match}/live/shout', [MatchCenterController::class, 'liveShout'])->name('matches.live.shout');
            Route::get('/matches/{match}/live/state', [MatchCenterController::class, 'liveState'])->name('matches.live.state');
            Route::get('/matches/{match}/live/state', [MatchCenterController::class, 'liveState'])->name('matches.live.state');
            Route::get('/lineups/match/{match}', [LineupsController::class, 'match'])->name('lineups.match');
            Route::get('/friendlies', [FriendlyMatchController::class, 'index'])->name('friendlies.index');
            Route::get('/friendlies', [FriendlyMatchController::class, 'index'])->name('friendlies.index');
            Route::post('/friendlies', [FriendlyMatchController::class, 'store'])->name('friendlies.store');
            Route::post('/friendlies/{friendlyRequest}/accept', [FriendlyMatchController::class, 'accept'])->name('friendlies.accept');
            Route::post('/friendlies/{friendlyRequest}/reject', [FriendlyMatchController::class, 'reject'])->name('friendlies.reject');
            Route::get('/table', [LeagueController::class, 'table'])->name('league.table');
            Route::get('/contracts', [ContractController::class, 'index'])->name('contracts.index');
            Route::post('/contracts/{player}/renew', [ContractController::class, 'renew'])->name('contracts.renew');
            Route::get('/sponsors', [SponsorController::class, 'index'])->name('sponsors.index');
            Route::post('/sponsors/{sponsor}/sign', [SponsorController::class, 'sign'])->name('sponsors.sign');
            Route::post('/sponsors/contracts/{contract}/terminate', [SponsorController::class, 'terminate'])->name('sponsors.contracts.terminate');
            Route::get('/stadium', [StadiumController::class, 'index'])->name('stadium.index');
            Route::post('/stadium/projects', [StadiumController::class, 'storeProject'])->name('stadium.projects.store');
            Route::get('/training-camps', [TrainingCampController::class, 'index'])->name('training-camps.index');
            Route::post('/training-camps', [TrainingCampController::class, 'store'])->name('training-camps.store');
            Route::get('/training', [TrainingController::class, 'index'])->name('training.index');
            Route::post('/training', [TrainingController::class, 'store'])->name('training.store');
            Route::post('/training/{session}/apply', [TrainingController::class, 'apply'])->name('training.apply');
            Route::get('/medical', [MedicalController::class, 'index'])->name('medical.index');
            Route::post('/medical/{player}/plan', [MedicalController::class, 'updatePlan'])->name('medical.plan.update');
            Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
            Route::post('/notifications/{notification}/seen', [NotificationController::class, 'markSeen'])->name('notifications.seen');
            Route::post('/notifications/seen-all', [NotificationController::class, 'markAllSeen'])->name('notifications.seen-all');
            Route::get('/manager-live', [ManagerLiveController::class, 'index'])->name('manager-live.index');
            Route::get('/finances', [FinanceController::class, 'index'])->name('finances.index');
            Route::get('/team-of-the-day', [TeamOfTheDayController::class, 'index'])->name('team-of-the-day.index');
            Route::post('/team-of-the-day/generate', [TeamOfTheDayController::class, 'generate'])->name('team-of-the-day.generate');
            Route::get('/teams/compare', [App\Http\Controllers\TeamComparisonController::class, 'index'])->name('teams.compare');
            Route::get('/statistics', [App\Http\Controllers\StatisticsController::class, 'index'])->name('statistics.index');
            Route::get('/awards', [AwardsController::class, 'index'])->name('awards.index');
            Route::get('/scouting', [ScoutingController::class, 'index'])->name('scouting.index');
            Route::post('/scouting/{player}/watchlist', [ScoutingController::class, 'storeWatchlist'])->name('scouting.watchlist.store');
            Route::patch('/scouting/watchlist/{watchlist}', [ScoutingController::class, 'updateWatchlist'])->name('scouting.watchlist.update');
            Route::delete('/scouting/watchlist/{watchlist}', [ScoutingController::class, 'destroyWatchlist'])->name('scouting.watchlist.destroy');
            Route::post('/scouting/{player}/report', [ScoutingController::class, 'generateReport'])->name('scouting.report.generate');
        }
    );
});

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('acp')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('competitions', AdminCompetitionController::class);
        Route::post('/competitions/{competition}/add-season', [AdminCompetitionController::class, 'addSeason'])->name('competitions.add-season');
        Route::resource('seasons', \App\Http\Controllers\Admin\SeasonController::class);
        Route::resource('competition-seasons', \App\Http\Controllers\Admin\CompetitionSeasonController::class)->only(['edit', 'update']);
        Route::resource('clubs', AdminClubController::class);
        Route::resource('players', AdminPlayerController::class);
        Route::resource('lineups', AdminLineupController::class);
        Route::post('/lineups/{lineup}/activate', [AdminLineupController::class, 'activate'])->name('lineups.activate');
        Route::post('/competition-seasons/{competitionSeason}/fixtures', [LeagueController::class, 'generateFixtures'])->name('competition-seasons.generate-fixtures');
        Route::post('/simulation/process-matchday', [AdminSimulationController::class, 'processMatchday'])->name('simulation.process-matchday')->middleware('throttle:heavy_task');

        Route::prefix('simulation/settings')->name('simulation.settings.')->group(
            function () {
                Route::get('/', [App\Http\Controllers\Admin\GeneralSimulationSettingsController::class, 'index'])->name('index');
                Route::post('/', [App\Http\Controllers\Admin\GeneralSimulationSettingsController::class, 'update'])->name('update');
            }
        );

        Route::resource('ticker-templates', \App\Http\Controllers\Admin\TickerTemplateController::class);

        // Match Engine Index
        Route::get('/match-engine', [App\Http\Controllers\Admin\GeneralSimulationSettingsController::class, 'index'])->name('match-engine.index');

        // Monitoring & Debug Center
        Route::get('/test/react', function () {
            return inertia('Test');
        })->name('test.react');

        Route::prefix('monitoring')->name('monitoring.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\MonitoringController::class, 'index'])->name('index');
            Route::get('/logs', [App\Http\Controllers\Admin\MonitoringController::class, 'logs'])->name('logs');
            Route::get('/analysis', [App\Http\Controllers\Admin\MonitoringController::class, 'analysis'])->name('analysis');
            Route::get('/lab', [App\Http\Controllers\Admin\MonitoringController::class, 'lab'])->name('lab');
            Route::get('/internals', [App\Http\Controllers\Admin\MonitoringController::class, 'internals'])->name('internals');
            Route::get('/scheduler', [App\Http\Controllers\Admin\MonitoringController::class, 'scheduler'])->name('scheduler');
            Route::delete('/logs/clear', [App\Http\Controllers\Admin\MonitoringController::class, 'clearLogs'])->name('logs.clear');
            Route::post('/repair', [App\Http\Controllers\Admin\MonitoringController::class, 'repair'])->name('repair');
            Route::post('/clear-cache', [App\Http\Controllers\Admin\MonitoringController::class, 'clearCache'])->name('clear-cache');
            Route::post('/lab/run', [App\Http\Controllers\Admin\MonitoringController::class, 'runLabSimulation'])->name('lab.run');
        });
    });

require __DIR__ . '/auth.php';
