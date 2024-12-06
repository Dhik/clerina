<?php

use App\Domain\Customer\Controllers\CustomerController;
use App\Domain\Customer\Controllers\CustomerAnalysisController;
use App\Domain\Customer\Controllers\CustomerNoteController;
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

        Route::prefix('customer')
            ->group(function () {
                Route::get('/', [CustomerController::class, 'index'])->name('customer.index');
                Route::get('/get', [CustomerController::class, 'getCustomer'])->name('customer.get');
                Route::get('/{customer}', [CustomerController::class, 'show'])->name('customer.show');
                Route::post('/export', [CustomerController::class, 'export'])->name('customer.export');
            });

        Route::prefix('customer-note')
            ->group(function() {
                Route::get('/get', [CustomerNoteController::class, 'getCustomerNote'])->name('customerNote.get');
                Route::post('/store', [CustomerNoteController::class, 'store'])->name('customerNote.store');
                Route::put('/update/{customerNote}', [CustomerNoteController::class, 'update'])->name('customerNote.update');
                Route::delete('/delete/{customerNote}', [CustomerNoteController::class, 'delete'])->name('customerNote.destroy');
            });

        Route::prefix('cstmr_analysis')
            ->group(function () {
                Route::get('/', [CustomerAnalysisController::class, 'index'])->name('customer_analysis.index');
                Route::get('/get', [CustomerAnalysisController::class, 'data'])->name('customer_analysis.data');
                Route::get('/import', [CustomerAnalysisController::class, 'importCustomers'])->name('customer_analysis.import');
                Route::get('/total', [CustomerAnalysisController::class, 'countUniqueCustomers'])->name('customer_analysis.total');
                Route::get('/product-pie', [CustomerAnalysisController::class, 'getProductCounts'])->name('customer_analysis.product_counts');
                Route::get('/city-pie', [CustomerAnalysisController::class, 'getCityCounts'])->name('customer_analysis.city_counts');

                Route::get('/daily-unique', [CustomerAnalysisController::class, 'getDailyUniqueCustomers'])->name('customer_analysis.daily_unique');
                Route::get('/export', [CustomerAnalysisController::class, 'export'])->name('customer_analysis.export');
                Route::get('/products', [CustomerAnalysisController::class, 'getProducts'])->name('customer_analysis.get_products');

                
                Route::get('/{id}', [CustomerAnalysisController::class, 'show'])->name('customer_analysis.show');
                Route::get('/{id}/product-distribution', [CustomerAnalysisController::class, 'productDistribution'])->name('customer_analysis.product_distribution');
                Route::get('/{id}/edit', [CustomerAnalysisController::class, 'edit'])->name('customer_analysis.edit');

                Route::post('/{id}/join', [CustomerAnalysisController::class, 'join'])->name('customer_analysis.join');
                Route::post('/{id}/unjoin', [CustomerAnalysisController::class, 'unjoin'])->name('customer_analysis.unjoin');
            });
    });
