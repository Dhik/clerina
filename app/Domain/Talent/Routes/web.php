<?php

use Illuminate\Support\Facades\Route;
use App\Domain\Talent\Controllers\TalentController;
use App\Domain\Talent\Controllers\TalentContentController;

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
    Route::prefix('talent')
        ->group(function () {

            Route::get('/', [TalentController::class, 'index'])->name('talent.index');
            Route::get('/data', [TalentController::class, 'data'])->name('talent.data');
            Route::post('/import', [TalentController::class, 'import'])->name('talent.import');
            Route::get('/download-template', [TalentController::class, 'downloadTalentTemplate'])->name('talent.downloadTemplate');
            Route::get('/create', [TalentController::class, 'create'])->name('talent.create');
            Route::post('/', [TalentController::class, 'store'])->name('talent.store');
            Route::get('/{talent}', [TalentController::class, 'show'])->name('talent.show');
            Route::get('/{talent}/edit', [TalentController::class, 'edit'])->name('talent.edit');
            Route::put('/{talent}', [TalentController::class, 'update'])->name('talent.update');
            Route::delete('{talent}', [TalentController::class, 'destroy'])->name('talent.destroy');
        });
    Route::prefix('tlnt-content')
        ->group(function () {
            Route::get('/', [TalentContentController::class, 'index'])->name('talent_content.index');
            Route::get('/talents', [TalentContentController::class, 'getTalents'])->name('talent_content.get');
            Route::get('/today', [TalentContentController::class, 'getTodayTalentNames'])->name('talent_content.today');
            Route::get('/calendar', [TalentContentController::class, 'calendar'])->name('talent_content.calendar');
            Route::get('/count', [TalentContentController::class, 'countContent'])->name('talent_content.count');
            Route::get('/data', [TalentContentController::class, 'data'])->name('talent_content.data');
            Route::get('/create', [TalentContentController::class, 'create'])->name('talent_content.create');
            Route::post('/', [TalentContentController::class, 'store'])->name('talent_content.store');
            Route::get('/{talentContent}', [TalentContentController::class, 'show'])->name('talent_content.show');
            Route::get('/{talentContent}/edit', [TalentContentController::class, 'edit'])->name('talent_content.edit');
            Route::put('/{talentContent}', [TalentContentController::class, 'update'])->name('talent_content.update');
            Route::delete('{talentContent}', [TalentContentController::class, 'destroy'])->name('talent_content.destroy');
        });
});
