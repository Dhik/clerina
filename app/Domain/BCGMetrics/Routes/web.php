<?php

use Illuminate\Support\Facades\Route;
use App\Domain\BCGMetrics\Controllers\BCGMetricsController;

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
        Route::prefix('bcg_metrics')
            ->group(function () {
                Route::get('/', [BCGMetricsController::class, 'index'])->name('bcg_metrics.index');
                Route::get('/get', [BCGMetricsController::class, 'get'])->name('bcg_metrics.get');
                Route::get('/chart-data', [BCGMetricsController::class, 'getChartData'])->name('bcg_metrics.get_chart_data');
                Route::get('/quadrant/{quadrant}', [BCGMetricsController::class, 'getQuadrantDetails'])->name('bcg_metrics.quadrant_details');
                Route::get('/import', [BCGMetricsController::class, 'importBcgProduct'])->name('bcg_metrics.import');
                Route::get('/import-stock', [BCGMetricsController::class, 'importBcgStock'])->name('bcg_metrics.import_stock');
                Route::get('/import-ads', [BCGMetricsController::class, 'importBcgAds'])->name('bcg_metrics.import_ads');
            });
    });