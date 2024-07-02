<?php

use App\Domain\Contest\Controllers\ContestContentController;
use App\Domain\Contest\Controllers\ContestController;
use Illuminate\Support\Facades\Route;

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
        Route::prefix('contest')
            ->group(function () {
                Route::get('/', [ContestController::class, 'index'])->name('contest.index');
                Route::get('/get', [ContestController::class, 'get'])->name('contest.get');
                Route::get('/create', [ContestController::class, 'create'])->name('contest.create');
                Route::post('/store', [ContestController::class, 'store'])->name('contest.store');
                Route::get('/edit/{contest}', [ContestController::class, 'edit'])->name('contest.edit');
                Route::put('/update/{contest}', [ContestController::class, 'update'])->name('contest.update');
                Route::get('/{contest}', [ContestController::class, 'show'])->name('contest.show');
                Route::delete('/destroy/{contest}', [ContestController::class, 'destroy'])->name('contest.destroy');
            });

        Route::prefix('contestContent')
            ->group(function () {
                Route::get('/create/{contest}', [ContestContentController::class, 'create'])->name('contestContent.create');
                Route::get('/refresh/{contestContent}', [ContestContentController::class, 'refresh'])->name('contestContent.refresh');
                Route::get('/bulkRefresh/{contest}', [ContestContentController::class, 'bulkRefresh'])->name('contestContent.bulkRefresh');
                Route::post('/store', [ContestContentController::class, 'store'])->name('contestContent.store');
                Route::get('/edit/{contestContent}', [ContestContentController::class, 'edit'])->name('contestContent.edit');
                Route::put('/update/{contestContent}', [ContestContentController::class, 'update'])->name('contestContent.update');
                Route::delete('/destroy/{contestContent}', [ContestContentController::class, 'destroy'])->name('contestContent.destroy');
                Route::get('/recap', [ContestContentController::class, 'getSalesRecap'])->name('contestContent.get-contestContent-recap');
                Route::get('/export/{contest}', [ContestContentController::class, 'export'])->name('contestContent.export'); // New export route
            });
    });

Route::get('admin/contestContent/get/{contest}', [ContestContentController::class, 'get'])->name('contestContent.get');
Route::get('admin/contestContent/get/{contest}/recap', [ContestContentController::class, 'getContestContentRecap'])->name('contestContent.get-recap');
Route::get('contest/{contest}', [ContestController::class, 'show'])->name('contest.public');
