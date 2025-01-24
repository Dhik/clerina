<?php

use App\Domain\Sales\Controllers\AdSpentMarketPlaceController;
use App\Domain\Sales\Controllers\AdSpentSocialMediaController;
use App\Domain\Sales\Controllers\SalesChannelController;
use App\Domain\Sales\Controllers\SalesController;
use App\Domain\Sales\Controllers\VisitController;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::prefix('api')
    ->middleware('api.key')
    ->group(function () {
        Route::get('/public-stats', [SalesController::class, 'forAISalesCleora']);
        Route::get('/sales_this_month_line_chart', [SalesController::class, 'getMonthlySalesChart']);
});

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

        // Ad spent social media
        Route::prefix('ad-spent-social-media')
            ->group(function () {
                Route::get('/', [AdSpentSocialMediaController::class, 'index'])->name('adSpentSocialMedia.index');
                Route::get('/get', [AdSpentSocialMediaController::class, 'get'])->name('adSpentSocialMedia.get');
                Route::get('/recap', [AdSpentSocialMediaController::class, 'getAdSpentRecap'])
                    ->name('adSpentSocialMedia.getAdSpentRecap');

                Route::post('/', [AdSpentSocialMediaController::class, 'store'])
                    ->name('adSpentSocialMedia.store');
            });

        // Ad spent social media
        Route::prefix('ad-spent-market-place')
            ->group(function () {
                Route::get('/', [AdSpentMarketPlaceController::class, 'index'])->name('adSpentMarketPlace.index');
                Route::get('/get', [AdSpentMarketPlaceController::class, 'get'])->name('adSpentMarketPlace.get');
                Route::get('/getByDate', [AdSpentMarketPlaceController::class, 'getByDate'])
                    ->name('adSpentMarketPlace.getByDate');
                Route::get('/recap', [AdSpentMarketPlaceController::class, 'getAdSpentRecap'])
                    ->name('adSpentMarketPlace.getAdSpentRecap');

                Route::post('/', [AdSpentMarketPlaceController::class, 'store'])
                    ->name('adSpentMarketPlace.store');
            });

        // Sales
        Route::prefix('sales')
            ->group(function () {
                Route::get('/', [SalesController::class, 'index'])->name('sales.index');
                Route::get('/net_sales', [SalesController::class, 'net_sales'])->name('sales.net_sales');
                Route::get('/get_net_sales', [SalesController::class, 'getNetProfit'])->name('sales.get_net_sales');
                Route::get('/get_net_sales_summary', [SalesController::class, 'getNetProfitSummary'])->name('sales.get_net_sales_summary');
                Route::get('/net_sales_line', [NetProfitController::class, 'getChartData'])->name('sales.net_sales_line');
                Route::get('/get', [SalesController::class, 'get'])->name('sales.get');
                Route::get('/omset/{date}', [SalesController::class, 'getOmsetByDate'])->name('sales.getOmsetByDate');
                Route::get('/send-message', [SalesController::class, 'sendMessageCleora']);
                Route::get('/import-sheet', [SalesController::class, 'importFromGoogleSheet'])->name('sales.import_ads');
                Route::get('/update-ads', [SalesController::class, 'updateMonthlyAdSpentData'])->name('sales.update_ads');
                Route::get('/waterfall-data', [SalesController::class, 'getWaterfallData'])->name('sales.waterfall-data');
                Route::get('/waterfall-data-2', [SalesController::class, 'getNetProfitMarginDaily'])->name('sales.waterfall-data-2');

                // Report
                Route::get('/sales-channel-donut-data', [SalesController::class, 'getSalesChannelDonutData'])->name('report.donut1');
                Route::get('/ads-channel-donut-data', [SalesController::class, 'getTotalAdSpentForDonutChart'])->name('report.donut2');
                Route::get('/kpi-status', [SalesController::class, 'getOrderStatusSummary'])->name('report.kpi-status');
                Route::get('/ads-spent-monthly', [SalesController::class, 'getTotalAdSpentPerSalesChannelAndSocialMedia'])->name('report.ads-spent-monthly');
                Route::get('/sales-channel-monthly', [SalesController::class, 'getTotalAmountPerSalesChannelPerMonth'])->name('report.sales-channel-monthly');

                Route::get('/recap', [SalesController::class, 'getSalesRecap'])->name('sales.get-sales-recap');
                Route::get('/meta_data', [SalesController::class, 'getAdInsights']);
                Route::get('/{sales}', [SalesController::class, 'show'])->name('sales.show');
                Route::get('/sync/{sales}', [SalesController::class, 'syncSales'])->name('sales.sales-sync');
            });

        // Sales channel
        Route::prefix('sales-channel')
            ->group(function () {
                Route::get('/', [SalesChannelController::class, 'index'])->name('salesChannel.index');
                Route::get('/get', [SalesChannelController::class, 'get'])->name('salesChannel.get');
                Route::post('/store', [SalesChannelController::class, 'store'])->name('salesChannel.store');
                Route::put('/update/{salesChannel}', [SalesChannelController::class, 'update'])
                    ->name('salesChannel.update');
                Route::delete('/destroy/{salesChannel}', [SalesChannelController::class, 'delete'])
                    ->name('salesChannel.destroy');
            });

        // Visit
        Route::prefix('visit')
            ->group(function () {
                Route::get('/', [VisitController::class, 'index'])->name('visit.index');
                Route::get('/get', [VisitController::class, 'get'])->name('visit.get');
                Route::get('/getByDate', [VisitController::class, 'getVisitByDate'])->name('visit.getByDate');
                Route::get('/recap', [VisitController::class, 'getVisitRecap'])->name('visit.get-visit-recap');

                Route::get('/import-cleora', [SalesController::class, 'importVisitCleora'])->name('visit.import_cleora');
                Route::get('/import-azrina', [SalesController::class, 'importVisitAzrina'])->name('visit.import_azrina');
                Route::get('/update', [SalesController::class, 'updateMonthlyVisitData'])->name('visit.update');

                Route::post('/', [VisitController::class, 'store'])->name('visit.store');

            });

        Route::prefix('main-report')
            ->group(function () {
                Route::get('/', [SalesController::class, 'report'])->name('report.index');
            });
    });
    
