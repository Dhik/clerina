<?php

use Illuminate\Support\Facades\Route;
use App\Domain\Product\Controllers\ProductController;

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
        Route::prefix('product')
            ->group(function () {

                Route::get('/', [ProductController::class, 'index'])->name('product.index');
                Route::get('/create', [ProductController::class, 'create'])->name('product.create');
                Route::get('/get', [ProductController::class, 'data'])->name('product.data');
                Route::get('/top-product', [ProductController::class, 'topProduct'])->name('product.top');
                Route::post('/', [ProductController::class, 'store'])->name('product.store');
                Route::get('/{product}', [ProductController::class, 'show'])->name('product.show');
                Route::get('/{product}/orders', [ProductController::class, 'getOrders'])->name('product.orders');
                Route::get('/{product}/marketing', [ProductController::class, 'getMarketingMetrics'])->name('product.marketing');
                Route::get('/{product}/sales', [ProductController::class, 'getSalesMetrics'])->name('product.sales');


                Route::get('/{product}/order-count-by-sku', [ProductController::class, 'getOrderCountBySku'])->name('product.getOrderCountBySku');
                Route::get('/{product}/order-count-per-day', [ProductController::class, 'getOrderCountPerDay'])->name('product.getOrderCountPerDay');
                Route::get('/{productId}/order-count-by-sales-channel', [ProductController::class, 'getOrderCountBySalesChannel'])->name('product.getOrderCountBySalesChannel');
                Route::get('/{productId}/talent-content', [ProductController::class, 'getTalentContent'])->name('product.talent-content');

                

                Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('product.edit');
                Route::put('/{product}', [ProductController::class, 'update'])->name('product.update');
                Route::delete('{product}', [ProductController::class, 'destroy'])->name('product.destroy');
            });
    });
