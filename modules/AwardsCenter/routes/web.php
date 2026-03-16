<?php

use App\Modules\AwardsCenter\Http\Controllers\AwardsCenterController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'has.club.or.admin'])->group(function () {
    Route::get('/awards', [AwardsCenterController::class, 'index'])->name('awards.index');
});
