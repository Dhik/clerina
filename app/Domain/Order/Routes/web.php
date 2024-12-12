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
                Route::get('/update', [OrderController::class, 'updateSalesTurnover'])->name('order.update_turnover');
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
    });
