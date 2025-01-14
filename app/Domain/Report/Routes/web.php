<?php

use Illuminate\Support\Facades\Route;
use App\Domain\Report\Controllers\ReportController;

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
        Route::prefix('report')
            ->group(function () {

                Route::get('/', [ReportController::class, 'index'])->name('reports.index');
                Route::get('/create', [ReportController::class, 'create'])->name('reports.create');
                Route::get('/get', [ReportController::class, 'data'])->name('reports.data');
                Route::post('/', [ReportController::class, 'store'])->name('reports.store');
                Route::get('/{report}', [ReportController::class, 'show'])->name('reports.show');
                Route::get('/{report}/edit', [ReportController::class, 'edit'])->name('reports.edit');
                Route::put('/{report}', [ReportController::class, 'update'])->name('reports.update');
                Route::delete('{report}', [ReportController::class, 'destroy'])->name('reports.destroy');
            });
    });
