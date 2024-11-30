<?php

use Illuminate\Support\Facades\Route;
use App\Domain\SpentTarget\Controllers\SpentTargetController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Route::prefix('admin')
    ->middleware('auth')
    ->group(function () {
    Route::prefix('spentTarget')
        ->group(function () {
    
            Route::get('/', [SpentTargetController::class, 'index'])->name('spentTarget.index');
            Route::get('data', [SpentTargetController::class, 'data'])->name('spentTarget.data');
            Route::get('/this-month', [SpentTargetController::class, 'getSpentTargetThisMonth'])->name('spentTarget.thisMonth');
            Route::get('/kol-by-day', [SpentTargetController::class, 'getTalentShouldGetByDay'])->name('spentTarget.byDay');
            Route::get('/ads-by-day', [SpentTargetController::class, 'getAdsSpentByDay'])->name('spentTarget.adsByDay');
            Route::get('/import-other-spent', [SpentTargetController::class, 'importOtherSpent'])->name('spentTarget.importOtherSpent');

            Route::get('/create', [SpentTargetController::class, 'create'])->name('spentTarget.create');
            Route::post('/', [SpentTargetController::class, 'store'])->name('spentTarget.store');
            Route::get('/{spentTarget}', [SpentTargetController::class, 'show'])->name('spentTarget.show');
            Route::get('/{spentTarget}/edit', [SpentTargetController::class, 'edit'])->name('spentTarget.edit');
            Route::put('/{spentTarget}', [SpentTargetController::class, 'update'])->name('spentTarget.update');
            Route::delete('{spentTarget}', [SpentTargetController::class, 'destroy'])->name('spentTarget.destroy');
        });
});

