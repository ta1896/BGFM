<?php

use App\Modules\MedicalCenter\Http\Controllers\MedicalCenterController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'has.club.or.admin'])->group(function () {
    Route::get('/medical', [MedicalCenterController::class, 'index'])->name('medical.index');
    Route::post('/medical/{player}/plan', [MedicalCenterController::class, 'updatePlan'])->name('medical.plan.update');
    Route::post('/medical/{player}/clearance', [MedicalCenterController::class, 'updateClearance'])->name('medical.clearance.update');
});
