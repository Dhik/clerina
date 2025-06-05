<?php

use App\Domain\Sales\Controllers\AdSpentMarketPlaceController;
use App\Domain\Sales\Controllers\AdSpentSocialMediaController;
use App\Domain\Sales\Controllers\SalesChannelController;
use App\Domain\Sales\Controllers\SalesController;
use App\Domain\Sales\Controllers\OperationalSpentController;
use App\Domain\Sales\Controllers\NetProfitController;
use App\Domain\Sales\Controllers\VisitController;
use App\Domain\Sales\Controllers\LaporanKeuanganController;
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
                Route::get('/campaign-summary', [AdSpentSocialMediaController::class, 'get_campaign_summary'])->name('adSpentSocialMedia.get_campaign_summary');
                Route::delete('/delete-by-account', [AdSpentSocialMediaController::class, 'deleteByAccountAndDate'])->name('adSpentSocialMedia.delete_by_account');
                Route::get('/export-data', [AdSpentSocialMediaController::class, 'exportAdsMetaStats'])->name('adSpentSocialMedia.export-data');

                Route::get('/get-shopee', [AdSpentSocialMediaController::class, 'get_ads_shopee'])->name('adSpentSocialMedia.get_ads_shopee');
                Route::post('/import-shopee-sku', [AdSpentSocialMediaController::class, 'importShopeeSkuDetails'])->name('adSpentSocialMedia.import_shopee_sku');
                Route::get('/show-shopee', [AdSpentSocialMediaController::class, 'get_shopee_details_by_date'])->name('adSpentSocialMedia.get_shopee_details_by_date');
                Route::get('/shopee-summary', [AdSpentSocialMediaController::class, 'get_shopee_summary'])->name('adSpentSocialMedia.get_shopee_summary');
                Route::post('/import-shopee', [AdSpentSocialMediaController::class, 'importShopeeAds'])->name('adSpentSocialMedia.import_shopee');
                Route::delete('/delete-shopee', [AdSpentSocialMediaController::class, 'deleteShopeeRecord'])->name('adSpentSocialMedia.delete_shopee');
                Route::get('/shopee-line-data', [AdSpentSocialMediaController::class, 'getShopeeLineData'])->name('adSpentSocialMedia.shopee_line_data');
                Route::get('/shopee-funnel-data', [AdSpentSocialMediaController::class, 'getShopeeFunnelData'])->name('adSpentSocialMedia.shopee_funnel_data');

                Route::get('/shopee2/get', [AdSpentSocialMediaController::class, 'get_shopee2_ads_cpas'])->name('adSpentSocialMedia.get_shopee2_ads_cpas');
                Route::post('/shopee2/import', [AdSpentSocialMediaController::class, 'import_shopee2_ads'])->name('adSpentSocialMedia.import_shopee2');
                Route::get('/shopee2/line-data', [AdSpentSocialMediaController::class, 'get_shopee2_line_data'])->name('adSpentSocialMedia.shopee2-line-data');
                Route::get('/shopee2/funnel-data', [AdSpentSocialMediaController::class, 'get_shopee2_funnel_data'])->name('adSpentSocialMedia.shopee2-funnel-data');
                
                // New routes for Shopee 3
                Route::get('/shopee3/get', [AdSpentSocialMediaController::class, 'get_shopee3_ads_cpas'])->name('adSpentSocialMedia.get_shopee3_ads_cpas');
                Route::post('/shopee3/import', [AdSpentSocialMediaController::class, 'import_shopee3_ads'])->name('adSpentSocialMedia.import_shopee3');
                Route::get('/shopee3/line-data', [AdSpentSocialMediaController::class, 'get_shopee3_line_data'])->name('adSpentSocialMedia.shopee3-line-data');
                Route::get('/shopee3/funnel-data', [AdSpentSocialMediaController::class, 'get_shopee3_funnel_data'])->name('adSpentSocialMedia.shopee3-funnel-data');
                
                // New routes for TikTok
                Route::get('/tiktok/get', [AdSpentSocialMediaController::class, 'get_tiktok_ads_cpas'])->name('adSpentSocialMedia.get_tiktok_ads_cpas');
                Route::get('/tiktok/show', [AdSpentSocialMediaController::class, 'get_tiktok_details_by_date'])->name('adSpentSocialMedia.get_tiktok_details_by_date');
                Route::get('/tiktok/campaign-summary', [AdSpentSocialMediaController::class, 'get_tiktok_campaign_summary'])->name('adSpentSocialMedia.get_tiktok_campaign_summary');
                Route::delete('/tiktok/delete-by-account', [AdSpentSocialMediaController::class, 'deleteTiktokByAccountAndDate'])->name('adSpentSocialMedia.delete_tiktok_by_account');
                Route::post('/tiktok/import', [AdSpentSocialMediaController::class, 'import_tiktok_ads'])->name('adSpentSocialMedia.import_tiktok');
                Route::get('/tiktok/line-data', [AdSpentSocialMediaController::class, 'get_tiktok_line_data'])->name('adSpentSocialMedia.tiktok-line-data');
                Route::get('/tiktok/funnel-data', [AdSpentSocialMediaController::class, 'get_tiktok_funnel_data'])->name('adSpentSocialMedia.tiktok-funnel-data');
                Route::post('/tiktok/import-gmv-max', [AdSpentSocialMediaController::class, 'import_tiktok_gmv_max'])->name('adSpentSocialMedia.import_tiktok_gmv_max');
                
                // New routes for Overall Performance
                Route::get('/overall/get', [AdSpentSocialMediaController::class, 'get_overall_performance'])->name('adSpentSocialMedia.get_overall_performance');
                Route::get('/platform-comparison', [AdSpentSocialMediaController::class, 'get_platform_comparison_data'])->name('adSpentSocialMedia.platform-comparison-data');
                Route::get('/overall-metrics', [AdSpentSocialMediaController::class, 'get_overall_metrics_data'])->name('adSpentSocialMedia.overall-metrics-data');
                Route::post('/export-report', [AdSpentSocialMediaController::class, 'export_overall_report'])->name('adSpentSocialMedia.export_overall_report');

                Route::get('/ads-monitoring/get', [AdSpentSocialMediaController::class, 'get_ads_monitoring'])->name('adSpentSocialMedia.get_ads_monitoring');
                Route::get('/ads-monitoring/chart-data', [AdSpentSocialMediaController::class, 'get_ads_monitoring_chart_data'])->name('adSpentSocialMedia.ads_monitoring_chart_data');
                Route::post('/ads-monitoring/export', [AdSpentSocialMediaController::class, 'export_ads_monitoring'])->name('adSpentSocialMedia.export_ads_monitoring');
                Route::post('/ads-monitoring/refresh/tiktok', [AdSpentSocialMediaController::class, 'refresh_tiktok_ads_monitoring'])->name('adSpentSocialMedia.refresh_tiktok_ads_monitoring');
                Route::post('/ads-monitoring/refresh/shopee', [AdSpentSocialMediaController::class, 'refresh_shopee_ads_monitoring'])->name('adSpentSocialMedia.refresh_shopee_ads_monitoring');
                Route::post('/ads-monitoring/refresh/meta', [AdSpentSocialMediaController::class, 'refresh_meta_ads_monitoring'])->name('adSpentSocialMedia.refresh_meta_ads_monitoring');
                Route::post('/ads-monitoring/refresh/all', [AdSpentSocialMediaController::class, 'refresh_all_ads_monitoring'])->name('adSpentSocialMedia.refresh_all_ads_monitoring');
                Route::get('/ads-monitoring/refresh/status', [AdSpentSocialMediaController::class, 'get_ads_monitoring_refresh_status'])->name('adSpentSocialMedia.get_ads_monitoring_refresh_status');

                Route::get('/spent-vs-gmv/get', [AdSpentSocialMediaController::class, 'get_spent_vs_gmv'])->name('adSpentSocialMedia.get_spent_vs_gmv');
                Route::get('/spent-vs-gmv/chart-data', [AdSpentSocialMediaController::class, 'get_spent_vs_gmv_chart_data'])->name('adSpentSocialMedia.spent_vs_gmv_chart_data');
                Route::post('/spent-vs-gmv/export', [AdSpentSocialMediaController::class, 'export_spent_vs_gmv'])->name('adSpentSocialMedia.export_spent_vs_gmv');
                                
                // Existing chart data routes
                // Route::get('/line-data', [AdSpentSocialMediaController::class, 'get_line_data'])->name('adSpentSocialMedia.line-data');
                // Route::get('/funnel-data', [AdSpentSocialMediaController::class, 'get_funnel_data'])->name('adSpentSocialMedia.funnel-data');
            });

        Route::prefix('operational-spent')->group(function () {
            Route::get('/', [OperationalSpentController::class, 'index'])->name('operational-spent.index');
            Route::get('/get', [OperationalSpentController::class, 'get'])->name('operational-spent.get');
            Route::get('/getByDate', [OperationalSpentController::class, 'getByDate'])->name('operational-spent.getByDate');
            Route::get('/getById', [OperationalSpentController::class, 'getById'])->name('operational-spent.getById'); // NEW ROUTE
            Route::post('/', [OperationalSpentController::class, 'store'])->name('operational-spent.store');
            Route::delete('/destroy', [OperationalSpentController::class, 'destroy'])->name('operational-spent.destroy');
        });

        Route::prefix('net-profit')->group(function () {
            Route::get('/update-spent-kol', [NetProfitController::class, 'updateSpentKol'])->name('net-profit.update-spent-kol');
            Route::get('/update-spent-kol-azrina', [NetProfitController::class, 'updateSpentKolAzrina'])->name('net-profit.update-spent-kol-azrina');
            Route::get('/update-b2b-crm', [NetProfitController::class, 'updateB2bAndCrmSales'])->name('net-profit.update-b2b-crm');
            Route::get('/update-b2b-crm-azrina', [NetProfitController::class, 'updateB2bAndCrmSalesAzrina'])->name('net-profit.update-b2b-crm-azrina');
            Route::get('/update-marketing', [NetProfitController::class, 'updateMarketing'])->name('net-profit.update-marketing');
            Route::get('/update-marketing-azrina', [NetProfitController::class, 'updateMarketingAzrina'])->name('net-profit.update-marketing-azrina');
            Route::get('/update-visit', [NetProfitController::class, 'updateVisit'])->name('net-profit.update-visit');
            Route::get('/update-visit-azrina', [NetProfitController::class, 'updateVisitAzrina'])->name('net-profit.update-visit-azrina');
            Route::get('/import-data', [NetProfitController::class, 'importNetProfits'])->name('net-profit.import-data');
            Route::get('/import-data-azrina', [NetProfitController::class, 'importNetProfitsAzrina'])->name('net-profit.import-data-azrina');
            Route::get('/export-data', [NetProfitController::class, 'exportCurrentMonthData'])->name('net-profit.export-data');
            Route::get('/export-lastmonth-azrina', [NetProfitController::class, 'exportLastMonthDataAzrina'])->name('net-profit.export-lastmonth-azrina');
            Route::get('/export-hpp', [NetProfitController::class, 'exportHPPLastMonth'])->name('net-profit.export-hpp');
            Route::get('/export-data-azrina', [NetProfitController::class, 'exportCurrentMonthDataAzrina'])->name('net-profit.export-data-azrina');
            Route::get('/export-lk', [NetProfitController::class, 'exportLK'])->name('net-profit.export-lk');
            Route::get('/export-unknown-orders', [NetProfitController::class, 'exportUnknownOrders'])->name('net-profit.export-unknown-orders');
            Route::get('/export-product-data', [NetProfitController::class, 'exportProductReport'])->name('net-profit.export-product-data');
            Route::get('/update-hpp', [NetProfitController::class, 'updateHpp'])->name('net-profit.update-hpp');
            Route::get('/update-hpp-azrina', [NetProfitController::class, 'updateHppAzrina'])->name('net-profit.update-hpp-azrina');
            Route::get('/hpp-by-date', [NetProfitController::class, 'getHppByDate'])->name('net-profit.getHppByDate');
            Route::get('/sales-by-channel', [NetProfitController::class, 'getSalesByChannel'])->name('net-profit.getSalesByChannel');
            Route::get('/update-roas', [NetProfitController::class, 'updateRoas'])->name('net-profit.update-roas');
            Route::get('/update-roas-azrina', [NetProfitController::class, 'updateRoasAzrina'])->name('net-profit.update-roas-azrina');
            Route::get('/update-sales', [NetProfitController::class, 'updateSales'])->name('net-profit.update-sales');
            Route::get('/update-sales-azrina', [NetProfitController::class, 'updateSalesAzrina'])->name('net-profit.update-sales-azrina');
            Route::get('/get_ad_spent_detail', [NetProfitController::class, 'getAdSpentDetail'])->name('net-profit.get_ad_spent_detail');
            Route::get('/update-qty', [NetProfitController::class, 'updateQty'])->name('net-profit.update-qty');
            Route::get('/update-qty-azrina', [NetProfitController::class, 'updateQtyAzrina'])->name('net-profit.update-qty-azrina');
            Route::get('/update-order-count', [NetProfitController::class, 'updateOrderCount'])->name('net-profit.update-order-count');
            Route::get('/update-order-count-azrina', [NetProfitController::class, 'updateOrderCountAzrina'])->name('net-profit.update-order-count-azrina');
            Route::get('/update-closing-rate', [NetProfitController::class, 'updateClosingRate'])->name('net-profit.update-closing-rate');
            Route::get('/sales-vs-marketing', [NetProfitController::class, 'getCurrentMonthCorrelation'])->name('net-profit.sales-vs-marketing');
            Route::get('/sales-optimization', [NetProfitController::class, 'getSalesOptimization'])->name('net-profit.sales-optimization');
            Route::get('/detail-sales-vs-marketing', [NetProfitController::class, 'getDetailCorrelation'])->name('net-profit.detail-sales-vs-marketing');
        });

        Route::prefix('lk')->group(function () {
            Route::get('/', [LaporanKeuanganController::class, 'index'])->name('lk.index');
            Route::get('/get', [LaporanKeuanganController::class, 'get'])->name('lk.get');
            Route::get('/summary', [LaporanKeuanganController::class, 'getSummary'])->name('lk.summary');
            Route::get('/details', [LaporanKeuanganController::class, 'getDetails'])->name('lk.details');
            Route::get('/gross-revenue-details', [LaporanKeuanganController::class, 'getGrossRevenueDetails'])->name('lk.gross_revenue_details');
        });

        // Sales
        Route::prefix('sales')
            ->group(function () {
                Route::get('/', [SalesController::class, 'index'])->name('sales.index');
                Route::get('/net_sales', [SalesController::class, 'net_sales'])->name('sales.net_sales');
                Route::get('/ads_relation', [SalesController::class, 'ads_relation'])->name('sales.ads_relation');
                Route::get('/net_per_channel', [SalesController::class, 'net_per_channel'])->name('sales.net_per_channel');
                Route::get('/get_net_sales', [SalesController::class, 'getNetProfit'])->name('sales.get_net_sales');
                Route::get('/get_sales_channels', [SalesController::class, 'getSalesChannels'])->name('sales.get_sales_channels');
                Route::get('/get_hpp_summary', [SalesController::class, 'getHPPChannelSummary'])->name('sales.get_hpp_summary');
                Route::get('/get_hpp_detail', [SalesController::class, 'getHppDetail'])->name('sales.get_hpp_detail');
                Route::get('/get_hpp_detail_total', [SalesController::class, 'getHppDetailTotal'])->name('sales.get_hpp_detail_total');
                Route::get('/get_net_sales_summary', [SalesController::class, 'getNetProfitSummary'])->name('sales.get_net_sales_summary');
                Route::get('/net_sales_line', [SalesController::class, 'getChartData'])->name('sales.net_sales_line');
                Route::get('/get', [SalesController::class, 'get'])->name('sales.get');
                Route::get('/omset/{date}', [SalesController::class, 'getOmsetByDate'])->name('sales.getOmsetByDate');
                Route::get('/send-message', [SalesController::class, 'sendMessageCleora']);
                Route::get('/import-sheet', [SalesController::class, 'importFromGoogleSheet'])->name('sales.import_ads');
                Route::get('/import-ads-azrina', [SalesController::class, 'importAdsAzrina'])->name('sales.import_ads_azrina');
                Route::get('/update-ads', [SalesController::class, 'updateMonthlyAdSpentData'])->name('sales.update_ads');
                Route::get('/update-ads-azrina', [SalesController::class, 'updateMonthlyAdSpentDataAzrina'])->name('sales.update_ads_azrina');
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
    
