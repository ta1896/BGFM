<?php

use App\Modules\ScoutingCenter\Http\Controllers\ScoutingCenterController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'has.club.or.admin'])->group(function () {
    Route::get('/scouting', [ScoutingCenterController::class, 'index'])->name('scouting.index');
    Route::post('/scouting/discover', [ScoutingCenterController::class, 'discoverTargets'])->name('scouting.discover');
    Route::post('/scouting/{player}/watchlist', [ScoutingCenterController::class, 'storeWatchlist'])->name('scouting.watchlist.store');
    Route::patch('/scouting/watchlist/{watchlist}', [ScoutingCenterController::class, 'updateWatchlist'])->name('scouting.watchlist.update');
    Route::post('/scouting/watchlist/{watchlist}/advance', [ScoutingCenterController::class, 'advanceWatchlist'])->name('scouting.watchlist.advance');
    Route::delete('/scouting/watchlist/{watchlist}', [ScoutingCenterController::class, 'destroyWatchlist'])->name('scouting.watchlist.destroy');
    Route::post('/scouting/{player}/report', [ScoutingCenterController::class, 'generateReport'])->name('scouting.report.generate');
});
