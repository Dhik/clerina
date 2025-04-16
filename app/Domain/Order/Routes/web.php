<?php

use App\Domain\Order\Controllers\OrderController;
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

Route::post('api/webhook', [OrderController::class, 'webhook']);
Route::post('api/update-status', [OrderController::class, 'updateStatus']);
Route::post('api/update-date-amount', [OrderController::class, 'updateDateAmount']);

Route::prefix('admin')
    ->middleware('auth')
    ->group(function () {
        Route::prefix('order')
            ->group(function () {
                Route::get('/', [OrderController::class, 'index'])->name('order.index');
                Route::get('/get', [OrderController::class, 'get'])->name('order.get');
                Route::get('/orders-by-date', [OrderController::class, 'getOrdersByDate'])->name('order.getOrdersByDate');
                Route::get('/fetch-external', [OrderController::class, 'fetchExternalOrders'])->name('order.fetch-external');
                Route::get('/fetch-all', [OrderController::class, 'fetchAllOrders'])->name('order.fetch-all');
                Route::get('/update-process-dates', [OrderController::class, 'updateProcessAtThisMonth']);
                Route::get('/update-status', [OrderController::class, 'fetchUpdateStatus'])->name('order.fetch-status');
                Route::get('/pie-status', [OrderController::class, 'getMonthlyOrderStatusDistribution'])->name('order.pie-status');
                Route::get('/daily-trend', [OrderController::class, 'getDailyStatusTrend'])->name('order.daily-trend');
                Route::get('/sku_qty', [OrderController::class, 'getSkuQuantities'])->name('order.sku_qty');
                Route::get('/sku_detail', [OrderController::class, 'getSkuDetail'])->name('order.sku_detail');
                Route::get('/hpp', [OrderController::class, 'getHPP'])->name('order.hpp');
                Route::get('/daily_hpp', [OrderController::class, 'getDailyHPP'])->name('order.daily_hpp');
                Route::get('/get_hpp', [OrderController::class, 'getHPPChannel'])->name('order.get_hpp');
                Route::get('/get_daily_hpp_summary', [OrderController::class, 'getHPPSummary'])->name('order.get_daily_hpp_summary');

                Route::get('/generate_hpp', [OrderController::class, 'generateDailyHpp'])->name('order.generate_hpp');
                Route::get('/sku_qty/export', [OrderController::class, 'exportSkuQuantities'])->name('order.sku_qty_export');
                Route::get('/sku_detail_qty', [OrderController::class, 'skuQuantities'])->name('order.sku_detail_qty');
                Route::get('/by-channel', [OrderController::class, 'getOrdersBySalesChannel'])->name('orders.by-channel');
                Route::get('/daily-by-sku', [OrderController::class, 'getDailyQuantityBySku'])->name('orders.daily-by-sku');
                Route::get('/qty-by-sku', [OrderController::class, 'getQuantityBySku'])->name('orders.qty-by-sku');
                Route::get('/daily-by-channel', [OrderController::class, 'getDailyOrdersByChannel'])->name('orders.daily-by-channel');
                Route::get('/import_customer', [OrderController::class, 'importOrdersCleora'])->name('order.import_customer');
                Route::get('/import_cleora_b2b', [OrderController::class, 'importCleoraB2B'])->name('order.import_cleora_b2b');
                Route::get('/import_azrina_b2b', [OrderController::class, 'importAzrinaB2B'])->name('order.import_azrina_b2b');
                Route::get('/import_cleora_crm', [OrderController::class, 'importClosingAnisa'])->name('order.import_cleora_crm');
                Route::get('/import_cleora_crm2', [OrderController::class, 'importClosingIis'])->name('order.import_cleora_crm2');
                Route::get('/import_cleora_crm3', [OrderController::class, 'importClosingKiki'])->name('order.import_cleora_crm3');
                Route::get('/import_cleora_crm4', [OrderController::class, 'importClosingZalsa'])->name('order.import_cleora_crm4');
                Route::get('/import_cleora_crm5', [OrderController::class, 'importClosingRina'])->name('order.import_cleora_crm5');
                Route::get('/import_balance_shopee', [OrderController::class, 'updateSuccessDateShopee'])->name('order.import_balance_shopee');
                Route::get('/import_balance_shopee2', [OrderController::class, 'updateSuccessDateShopee2'])->name('order.import_balance_shopee2');
                Route::get('/import_balance_shopee3', [OrderController::class, 'updateSuccessDateShopee3'])->name('order.import_balance_shopee3');
                Route::get('/import_balance_tiktok', [OrderController::class, 'updateSuccessDateTiktok'])->name('order.import_balance_tiktok');
                Route::get('/import_balance_lazada', [OrderController::class, 'updateSuccessDateLazada'])->name('order.import_balance_lazada');
                Route::get('/import_balance_tokped', [OrderController::class, 'updateSuccessDateTokopedia'])->name('order.import_balance_tokped');
                Route::get('/import_crm_customer', [OrderController::class, 'importCRMCustomer'])->name('order.import_crm_customer');
                Route::get('/import_tokped', [OrderController::class, 'importOrdersTokopedia'])->name('order.import_tokped');
                Route::get('/cleora_shopee', [OrderController::class, 'importOrdersShopee'])->name('order.cleora_shopee');
                Route::get('/cleora_tiktok', [OrderController::class, 'importOrdersTiktok'])->name('order.cleora_tiktok');
                Route::get('/cleora_lazada', [OrderController::class, 'importOrdersLazada'])->name('order.cleora_lazada');
                Route::get('/import_shopee2', [OrderController::class, 'importOrdersShopee2'])->name('order.import_shopee2');
                Route::get('/import_shopee3', [OrderController::class, 'importOrdersShopee3'])->name('order.import_shopee3');

                Route::get('/azrina_shopee', [OrderController::class, 'importAzrinaShopee'])->name('order.azrina_shopee');
                Route::get('/azrina_tiktok', [OrderController::class, 'importAzrinaTiktok'])->name('order.azrina_tiktok');
                Route::get('/azrina_lazada', [OrderController::class, 'importAzrinaLazada'])->name('order.azrina_lazada');
                Route::get('/azrina_tokped', [OrderController::class, 'importAzrinaTokopedia'])->name('order.azrina_tokped');

                Route::get('/update', [OrderController::class, 'updateSalesTurnover'])->name('order.update_turnover');
                Route::get('/update_azrina', [OrderController::class, 'updateSalesTurnover2'])->name('order.update_turnover_azrina');
                Route::get('/export-unique-skus', [OrderController::class, 'exportUniqueSku']);

                Route::get('/exportTemplate', [OrderController::class, 'downloadTemplate'])
                    ->name('order.download-template');
                Route::post('/export', [OrderController::class, 'export'])->name('order.export');
                Route::post('/import', [OrderController::class, 'import'])->name('order.import');

                Route::get('/{order}', [OrderController::class, 'show'])->name('order.show');
                Route::post('/', [OrderController::class, 'store'])->name('order.store');
                Route::put('/{order}', [OrderController::class, 'update'])->name('order.update');
                Route::delete('{order}', [OrderController::class, 'destroy'])->name('order.destroy');
                
            });
        Route::prefix('producte')
            ->group(function () {
                Route::get('/', [OrderController::class, 'product'])->name('order.product');
                Route::get('/get', [OrderController::class, 'getPerformanceData'])->name('order.getProduct');
            });
        
        Route::get('/demography', [OrderController::class, 'showDemography']);
    });
