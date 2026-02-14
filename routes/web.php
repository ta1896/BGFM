<?php

use App\Http\Controllers\Admin\ClubController as AdminClubController;
use App\Http\Controllers\Admin\CompetitionController as AdminCompetitionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LineupController as AdminLineupController;
use App\Http\Controllers\Admin\PlayerController as AdminPlayerController;
use App\Http\Controllers\Admin\SimulationController as AdminSimulationController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\FreeClubController;
use App\Http\Controllers\FriendlyMatchController;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\LineupController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MatchCenterController;
use App\Http\Controllers\MatchLineupController;
use App\Http\Controllers\NationalTeamController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RandomEventController;
use App\Http\Controllers\SponsorController;
use App\Http\Controllers\StadiumController;
use App\Http\Controllers\TeamOfTheDayController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\TrainingCampController;
use App\Http\Controllers\TransferMarketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class , 'index'])->name('dashboard');
    Route::get('/freie-vereine', [FreeClubController::class , 'index'])->name('clubs.free');
    Route::post('/freie-vereine/{club}/uebernehmen', [FreeClubController::class , 'claim'])->name('clubs.claim');

    Route::get('/profile', [ProfileController::class , 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class , 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class , 'destroy'])->name('profile.destroy');

    Route::middleware('has.club.or.admin')->group(function () {
            Route::resource('clubs', ClubController::class)->only(['index', 'show']);
            Route::resource('players', PlayerController::class)->only(['index', 'show']);
            Route::resource('lineups', LineupController::class);
            Route::post('/lineups/{lineup}/activate', [LineupController::class , 'activate'])->name('lineups.activate');
            Route::get('/matches', [LeagueController::class , 'matches'])->name('league.matches');
            Route::get('/matches/{match}', [MatchCenterController::class , 'show'])->name('matches.show');
            Route::post('/matches/{match}/simulate', [MatchCenterController::class , 'simulate'])->name('matches.simulate');
            Route::post('/matches/{match}/live/start', [MatchCenterController::class , 'liveStart'])->name('matches.live.start');
            Route::post('/matches/{match}/live/resume', [MatchCenterController::class , 'liveResume'])->name('matches.live.resume');
            Route::post('/matches/{match}/live/style', [MatchCenterController::class , 'liveSetStyle'])->name('matches.live.style');
            Route::post('/matches/{match}/live/substitute', [MatchCenterController::class , 'liveSubstitute'])->name('matches.live.substitute');
            Route::post('/matches/{match}/live/substitute/plan', [MatchCenterController::class , 'livePlanSubstitute'])->name('matches.live.substitute.plan');
            Route::get('/matches/{match}/live/state', [MatchCenterController::class , 'liveState'])->name('matches.live.state');
            Route::get('/matches/{match}/lineup', [MatchLineupController::class , 'edit'])->name('matches.lineup.edit');
            Route::post('/matches/{match}/lineup', [MatchLineupController::class , 'update'])->name('matches.lineup.update');
            Route::post('/matches/{match}/lineup/load-template', [MatchLineupController::class , 'loadTemplate'])->name('matches.lineup.load-template');
            Route::post('/matches/{match}/lineup/auto-pick', [MatchLineupController::class , 'autoPick'])->name('matches.lineup.auto-pick');
            Route::delete('/matches/{match}/lineup/templates/{template}', [MatchLineupController::class , 'destroyTemplate'])->name('matches.lineup.template.destroy');
            Route::get('/friendlies', [FriendlyMatchController::class , 'index'])->name('friendlies.index');
            Route::post('/friendlies', [FriendlyMatchController::class , 'store'])->name('friendlies.store');
            Route::post('/friendlies/{friendlyRequest}/accept', [FriendlyMatchController::class , 'accept'])->name('friendlies.accept');
            Route::post('/friendlies/{friendlyRequest}/reject', [FriendlyMatchController::class , 'reject'])->name('friendlies.reject');
            Route::get('/table', [LeagueController::class , 'table'])->name('league.table');
            Route::get('/transfers', [TransferMarketController::class , 'index'])->name('transfers.index');
            Route::post('/transfers/listings', [TransferMarketController::class , 'storeListing'])->name('transfers.listings.store');
            Route::post('/transfers/listings/{listing}/bids', [TransferMarketController::class , 'placeBid'])->name('transfers.bids.store');
            Route::post('/transfers/listings/{listing}/accept/{bid}', [TransferMarketController::class , 'acceptBid'])->name('transfers.bids.accept');
            Route::post('/transfers/listings/{listing}/close', [TransferMarketController::class , 'closeListing'])->name('transfers.listings.close');
            Route::get('/loans', [LoanController::class , 'index'])->name('loans.index');
            Route::post('/loans/listings', [LoanController::class , 'storeListing'])->name('loans.listings.store');
            Route::post('/loans/listings/{listing}/bids', [LoanController::class , 'placeBid'])->name('loans.bids.store');
            Route::post('/loans/listings/{listing}/accept/{bid}', [LoanController::class , 'acceptBid'])->name('loans.bids.accept');
            Route::post('/loans/listings/{listing}/close', [LoanController::class , 'closeListing'])->name('loans.listings.close');
            Route::post('/loans/{loan}/option/exercise', [LoanController::class , 'exerciseOption'])->name('loans.option.exercise');
            Route::post('/loans/{loan}/option/decline', [LoanController::class , 'declineOption'])->name('loans.option.decline');
            Route::get('/contracts', [ContractController::class , 'index'])->name('contracts.index');
            Route::post('/contracts/{player}/renew', [ContractController::class , 'renew'])->name('contracts.renew');
            Route::get('/sponsors', [SponsorController::class , 'index'])->name('sponsors.index');
            Route::post('/sponsors/{sponsor}/sign', [SponsorController::class , 'sign'])->name('sponsors.sign');
            Route::post('/sponsors/contracts/{contract}/terminate', [SponsorController::class , 'terminate'])->name('sponsors.contracts.terminate');
            Route::get('/stadium', [StadiumController::class , 'index'])->name('stadium.index');
            Route::post('/stadium/projects', [StadiumController::class , 'storeProject'])->name('stadium.projects.store');
            Route::get('/training-camps', [TrainingCampController::class , 'index'])->name('training-camps.index');
            Route::post('/training-camps', [TrainingCampController::class , 'store'])->name('training-camps.store');
            Route::get('/training', [TrainingController::class , 'index'])->name('training.index');
            Route::post('/training', [TrainingController::class , 'store'])->name('training.store');
            Route::post('/training/{session}/apply', [TrainingController::class , 'apply'])->name('training.apply');
            Route::get('/notifications', [NotificationController::class , 'index'])->name('notifications.index');
            Route::post('/notifications/{notification}/seen', [NotificationController::class , 'markSeen'])->name('notifications.seen');
            Route::post('/notifications/seen-all', [NotificationController::class , 'markAllSeen'])->name('notifications.seen-all');
            Route::get('/finances', [FinanceController::class , 'index'])->name('finances.index');
            Route::get('/national-teams', [NationalTeamController::class , 'index'])->name('national-teams.index');
            Route::post('/national-teams/{nationalTeam}/refresh', [NationalTeamController::class , 'refresh'])->name('national-teams.refresh');
            Route::get('/team-of-the-day', [TeamOfTheDayController::class , 'index'])->name('team-of-the-day.index');
            Route::post('/team-of-the-day/generate', [TeamOfTheDayController::class , 'generate'])->name('team-of-the-day.generate');
            Route::get('/random-events', [RandomEventController::class , 'index'])->name('random-events.index');
            Route::post('/random-events/trigger', [RandomEventController::class , 'trigger'])->name('random-events.trigger');
            Route::post('/random-events/{occurrence}/apply', [RandomEventController::class , 'apply'])->name('random-events.apply');
        }
        );    });

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('acp')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class , 'index'])->name('dashboard');
        Route::resource('competitions', AdminCompetitionController::class);
        Route::resource('clubs', AdminClubController::class);
        Route::resource('players', AdminPlayerController::class);
        Route::resource('lineups', AdminLineupController::class);
        Route::post('/lineups/{lineup}/activate', [AdminLineupController::class , 'activate'])->name('lineups.activate');
        Route::post('/competition-seasons/{competitionSeason}/fixtures', [LeagueController::class , 'generateFixtures'])->name('competition-seasons.generate-fixtures');
        Route::post('/simulation/process-matchday', [AdminSimulationController::class , 'processMatchday'])->name('simulation.process-matchday');

        Route::prefix('simulation/settings')->name('simulation.settings.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\GeneralSimulationSettingsController::class , 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Admin\GeneralSimulationSettingsController::class , 'update'])->name('update');
        }
        );

        // Match Engine Index (pointing to settings for now as it seems to be the intended target)
        Route::get('/match-engine', [App\Http\Controllers\Admin\GeneralSimulationSettingsController::class , 'index'])->name('match-engine.index');
    });

require __DIR__ . '/auth.php';
