<?php

use Illuminate\Support\Facades\Route;
use App\Domain\KeywordMonitoring\Controllers\KeywordMonitoringController;

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
        Route::prefix('keywordMonitoring')
            ->group(function () {
                Route::get('/', [KeywordMonitoringController::class, 'index'])->name('keywordMonitoring.index');
                Route::get('/data', [KeywordMonitoringController::class, 'data'])->name('keywordMonitoring.data');
                Route::get('/get-all-postings', [KeywordMonitoringController::class, 'getAllPostings'])->name('keywordMonitoring.getAllPostings');
                Route::get('/create', [KeywordMonitoringController::class, 'create'])->name('keywordMonitoring.create');
                Route::post('/', [KeywordMonitoringController::class, 'store'])->name('keywordMonitoring.store');
                Route::get('/{keywordMonitoring}', [KeywordMonitoringController::class, 'show'])->name('keywordMonitoring.show');
                Route::get('/{keywordMonitoring}/edit', [KeywordMonitoringController::class, 'edit'])->name('keywordMonitoring.edit');
                Route::put('/{keywordMonitoring}', [KeywordMonitoringController::class, 'update'])->name('keywordMonitoring.update');
                Route::delete('/{keywordMonitoring}', [KeywordMonitoringController::class, 'destroy'])->name('keywordMonitoring.destroy');
                Route::get('/fetch-tiktok-data/{id}', [KeywordMonitoringController::class, 'fetchTiktokData'])->name('keywordMonitoring.fetchTiktokData');
                Route::get('/get-postings-data/{id}', [KeywordMonitoringController::class, 'getPostingsData'])->name('keywordMonitoring.getPostingsData');
            });
    });

