<?php

use App\Domain\Sales\Controllers\AdSpentMarketPlaceController;
use App\Domain\Sales\Controllers\AdSpentSocialMediaController;
use App\Domain\Sales\Controllers\SalesChannelController;
use App\Domain\Sales\Controllers\SalesController;
use App\Domain\Sales\Controllers\OperationalSpentController;
use App\Domain\Sales\Controllers\NetProfitController;
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

        Route::prefix('ad-spent-social-media')
            ->group(function () {
                Route::get('/', [AdSpentSocialMediaController::class, 'index'])->name('adSpentSocialMedia.index');
                Route::get('/get', [AdSpentSocialMediaController::class, 'get'])->name('adSpentSocialMedia.get');
                Route::post('/import', [AdSpentSocialMediaController::class, 'import_ads'])->name('adSpentSocialMedia.import');
                Route::get('/funnel-data', [AdSpentSocialMediaController::class, 'getFunnelData'])->name('adSpentSocialMedia.funnel-data');
                Route::get('/line-data', [AdSpentSocialMediaController::class, 'getImpressionChartData'])->name('adSpentSocialMedia.line-data');
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
        
        Route::prefix('ads_cpas')
            ->group(function () {
                Route::get('/', [AdSpentSocialMediaController::class, 'ads_cpas_index'])->name('adSpentSocialMedia.ads_cpas_index');
                Route::get('/get', [AdSpentSocialMediaController::class, 'get_ads_cpas'])->name('adSpentSocialMedia.get_ads_cpas');
                Route::get('/show', [AdSpentSocialMediaController::class, 'get_ads_details_by_date'])->name('adSpentSocialMedia.get_details_by_date');
                Route::delete('/delete-by-account', [AdSpentSocialMediaController::class, 'deleteByAccountAndDate'])->name('adSpentSocialMedia.delete_by_account');
            });

        Route::prefix('operational-spent')->group(function () {
            Route::get('/', [OperationalSpentController::class, 'index'])->name('operational-spent.index');
            Route::get('/get', [OperationalSpentController::class, 'get'])->name('operational-spent.get');
            Route::get('/getByDate', [OperationalSpentController::class, 'getByDate'])->name('operational-spent.getByDate');
            Route::post('/', [OperationalSpentController::class, 'store'])->name('operational-spent.store');
        });

        Route::prefix('net-profit')->group(function () {
            Route::get('/update-spent-kol', [NetProfitController::class, 'updateSpentKol'])->name('net-profit.update-spent-kol');
            // Route::get('/update-spent-kol-azrina', [NetProfitController::class, 'updateSpentKolAzrina'])->name('net-profit.update-spent-kol-azrina');
            Route::get('/update-b2b-crm', [NetProfitController::class, 'updateB2bAndCrmSales'])->name('net-profit.update-b2b-crm');
            Route::get('/update-marketing', [NetProfitController::class, 'updateMarketing'])->name('net-profit.update-marketing');
            Route::get('/import-data', [NetProfitController::class, 'importNetProfits'])->name('net-profit.import-data');
            Route::get('/export-data', [NetProfitExportController::class, 'exportDateAndSales'])->name('net-profit.export-data');
            Route::get('/update-hpp', [NetProfitController::class, 'updateHpp'])->name('net-profit.update-hpp');
            Route::get('/hpp-by-date', [NetProfitController::class, 'getHppByDate'])->name('net-profit.getHppByDate');
            Route::get('/update-roas', [NetProfitController::class, 'updateRoas'])->name('net-profit.update-roas');
            Route::get('/update-sales', [NetProfitController::class, 'updateSales'])->name('net-profit.update-sales');
            Route::get('/get_ad_spent_detail', [NetProfitController::class, 'getAdSpentDetail'])->name('net-profit.get_ad_spent_detail');
            Route::get('/update-qty', [NetProfitController::class, 'updateQty'])->name('net-profit.update-qty');
            Route::get('/update-order-count', [NetProfitController::class, 'updateOrderCount'])->name('net-profit.update-order-count');
            Route::get('/update-closing-rate', [NetProfitController::class, 'updateClosingRate'])->name('net-profit.update-closing-rate');
            Route::get('/sales-vs-marketing', [NetProfitController::class, 'getCurrentMonthCorrelation'])->name('net-profit.sales-vs-marketing');
        });

        // Sales
        Route::prefix('sales')
            ->group(function () {
                Route::get('/', [SalesController::class, 'index'])->name('sales.index');
                Route::get('/net_sales', [SalesController::class, 'net_sales'])->name('sales.net_sales');
                Route::get('/net_per_channel', [SalesController::class, 'net_per_channel'])->name('sales.net_per_channel');
                Route::get('/get_net_sales', [SalesController::class, 'getNetProfit'])->name('sales.get_net_sales');
                Route::get('/get_hpp_summary', [SalesController::class, 'getHPPChannelSummary'])->name('sales.get_hpp_summary');
                Route::get('/get_hpp_detail', [SalesController::class, 'getHppDetail'])->name('sales.get_hpp_detail');
                Route::get('/get_hpp_detail_total', [SalesController::class, 'getHppDetailTotal'])->name('sales.get_hpp_detail_total');
                Route::get('/get_net_sales_summary', [SalesController::class, 'getNetProfitSummary'])->name('sales.get_net_sales_summary');
                Route::get('/net_sales_line', [SalesController::class, 'getChartData'])->name('sales.net_sales_line');
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
    
