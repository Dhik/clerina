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
                // Main dashboard
                Route::get('/', [BCGMetricsController::class, 'index'])->name('bcg_metrics.index');
                
                // Analysis dashboard
                Route::get('/analysis/{date?}', [BCGMetricsController::class, 'getAnalysisDashboard'])->name('bcg_metrics.analysis');
                
                // API endpoints for chart and data
                Route::get('/chart-data', [BCGMetricsController::class, 'getChartData'])->name('bcg_metrics.get_chart_data');
                Route::get('/recommendations/{date?}', [BCGMetricsController::class, 'getRecommendations'])->name('bcg_metrics.get_recommendations');
                Route::get('/quadrant/{quadrant}', [BCGMetricsController::class, 'getQuadrantDetails'])->name('bcg_metrics.quadrant_details');
                Route::get('/product/{kode_produk}', [BCGMetricsController::class, 'getProductDetails'])->name('bcg_metrics.product_details');
                
                // Advanced filtering and search
                Route::get('/advanced-filter', [BCGMetricsController::class, 'advancedFilter'])->name('bcg_metrics.advanced_filter');
                Route::get('/trends', [BCGMetricsController::class, 'getPerformanceTrends'])->name('bcg_metrics.trends');
                
                // Export functionality
                Route::get('/export/{format}/{date?}', [BCGMetricsController::class, 'exportAnalysis'])->name('bcg_metrics.export');
                
                // Bulk operations
                Route::post('/update-strategies', [BCGMetricsController::class, 'updateProductStrategies'])->name('bcg_metrics.update_strategies');
                
                // Data import routes
                Route::get('/import', [BCGMetricsController::class, 'importBcgProduct'])->name('bcg_metrics.import');
                Route::get('/import-stock', [BCGMetricsController::class, 'importBcgStock'])->name('bcg_metrics.import_stock');
                Route::get('/import-ads', [BCGMetricsController::class, 'importBcgAds'])->name('bcg_metrics.import_ads');
                
                // Legacy route
                Route::get('/get', [BCGMetricsController::class, 'get'])->name('bcg_metrics.get');
            });
    });