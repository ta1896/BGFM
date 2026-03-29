<?php

use Illuminate\Support\Facades\Route;
use App\Modules\DataCenter\Http\Controllers\Admin\LeagueImporterController;

Route::middleware(['web', 'auth', 'admin'])->prefix('admin/data-center')->name('admin.data-center.')->group(function () {
    Route::get('/league-importer', [LeagueImporterController::class, 'index'])->name('league-importer.index');
    Route::post('/league-importer', [LeagueImporterController::class, 'store'])->name('league-importer.store');
    Route::delete('/league-importer/clear', [LeagueImporterController::class, 'clear'])->name('league-importer.clear');
    Route::post('/sofascore-finder', [LeagueImporterController::class, 'findSofascoreIds'])->name('sofascore-finder.store');
});

