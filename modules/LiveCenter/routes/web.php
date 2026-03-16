<?php

use App\Modules\LiveCenter\Http\Controllers\LiveCenterController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'has.club.or.admin'])->group(function () {
    Route::get('/manager-live', [LiveCenterController::class, 'index'])->name('manager-live.index');
    Route::get('/live-ticker', [LiveCenterController::class, 'ticker'])->name('live-ticker.index');
});
