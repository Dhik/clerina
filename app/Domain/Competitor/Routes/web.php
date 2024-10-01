<?php

use Illuminate\Support\Facades\Route;
use App\Domain\Competitor\Controllers\CompetitorController;
use App\Domain\Competitor\Controllers\CompetitorBrandController;
use App\Domain\Competitor\Controllers\CompetitorSalesController;
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
        Route::prefix('competitor_brands')
        ->group(function () {
            // Display the list of competitor brands (index)
            Route::get('/', [CompetitorBrandController::class, 'index'])->name('competitor_brands.index');
            Route::get('/create', [CompetitorBrandController::class, 'create'])->name('competitor_brands.create');
            Route::post('/', [CompetitorBrandController::class, 'store'])->name('competitor_brands.store');
            Route::get('/data', [CompetitorBrandController::class, 'data'])->name('competitor_brands.data');
            Route::get('/monthly-sales', [CompetitorBrandController::class, 'getMonthlySalesData'])->name('competitor_brands.monthly_sales');
            Route::get('/data/{competitorBrandId}', [CompetitorBrandController::class, 'getCompetitorSalesData'])->name('competitor_brand.sales_data');
            Route::get('/{competitorBrandId}/sales-chart', [CompetitorBrandController::class, 'getCompetitorSalesChart'])->name('competitor_brands.sales_chart');
            Route::get('/sales/{id}', [CompetitorBrandController::class, 'show_sales'])->name('competitor_brand.show_sales');
            Route::get('/{competitorBrand}', [CompetitorBrandController::class, 'show'])->name('competitor_brands.show');
            Route::get('/{competitorBrand}/edit', [CompetitorBrandController::class, 'edit'])->name('competitor_brands.edit');
            Route::put('/{competitorBrand}', [CompetitorBrandController::class, 'update'])->name('competitor_brands.update');
            Route::delete('/{competitorBrand}', [CompetitorBrandController::class, 'destroy'])->name('competitor_brands.destroy');
        });

        Route::prefix('competitor_sales')
        ->group(function () {
            
            Route::get('/', [CompetitorSalesController::class, 'index'])->name('competitor_sales.index');
            Route::post('/', [CompetitorSalesController::class, 'store'])->name('competitor_sales.store');
            Route::get('/data', [CompetitorSalesController::class, 'data'])->name('competitor_sales.data');
            Route::get('/{competitorSale}', [CompetitorSalesController::class, 'show'])->name('competitor_sales.show');
            Route::get('/{competitorSale}/edit', [CompetitorSalesController::class, 'edit'])->name('competitor_sales.edit');
            Route::put('/{competitorSale}', [CompetitorSalesController::class, 'update'])->name('competitor_sales.update');
            Route::delete('/{competitorSale}', [CompetitorSalesController::class, 'destroy'])->name('competitor_sales.destroy');
        });
    });


