<?php

use App\Domain\Affiliate\Controllers\AffiliateShopeeController;
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
        Route::prefix('affiliate_shopee')->group(function () {
            Route::get('/', [AffiliateShopeeController::class, 'index'])->name('affiliate_shopee.index');
            Route::get('/get-data', [AffiliateShopeeController::class, 'get_affiliate_shopee'])->name('affiliate_shopee.get_data');
            Route::get('/get-details-by-date', [AffiliateShopeeController::class, 'get_affiliate_shopee_details_by_date'])->name('affiliate_shopee.get_details_by_date');
            Route::get('/line-data', [AffiliateShopeeController::class, 'get_line_data'])->name('affiliate_shopee.line_data');
            Route::get('/funnel-data', [AffiliateShopeeController::class, 'get_funnel_data'])->name('affiliate_shopee.funnel_data');
            Route::post('/import', [AffiliateShopeeController::class, 'import_affiliate_shopee'])->name('affiliate_shopee.import');
            Route::delete('/delete', [AffiliateShopeeController::class, 'delete_affiliate_shopee'])->name('affiliate_shopee.delete');
        });
    });