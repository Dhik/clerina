<?php

use Illuminate\Support\Facades\Route;
use App\Domain\Talent\Controllers\TalentController;

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
});
