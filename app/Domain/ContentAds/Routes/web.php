<?php

use Illuminate\Support\Facades\Route;
use App\Domain\ContentAds\Controllers\ContentAdsController;

/*
|--------------------------------------------------------------------------
| Web Routes for Content Ads
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware('auth')
    ->group(function () {
        Route::prefix('contentAds')
            ->group(function () {

                // API Routes for data processing (PUT THESE FIRST)
                Route::get('/data', [ContentAdsController::class, 'data'])->name('contentAds.data');
                Route::get('/kpi-data', [ContentAdsController::class, 'getKpiData'])->name('contentAds.kpiData');
                Route::get('/import_content_ads', [ContentAdsController::class, 'importContentAdsFromGSheet'])->name('contentAds.import_gsheet');
                Route::post('/store', [ContentAdsController::class, 'store'])->name('contentAds.store');

                // Standard CRUD routes (THESE COME AFTER)
                Route::get('/', [ContentAdsController::class, 'index'])->name('contentAds.index');
                Route::get('/{contentAds}', [ContentAdsController::class, 'show'])->name('contentAds.show');
                Route::put('/{contentAds}/update', [ContentAdsController::class, 'update'])->name('contentAds.update');
                Route::delete('/{contentAds}/destroy', [ContentAdsController::class, 'destroy'])->name('contentAds.destroy');
                Route::get('/{contentAds}/details', [ContentAdsController::class, 'getDetails'])->name('contentAds.details');

                // Step-specific routes
                Route::get('/{contentAds}/step/{step}', [ContentAdsController::class, 'editStep'])
                    ->name('contentAds.editStep')
                    ->where('step', '[1-3]');
                
                Route::put('/{contentAds}/step/{step}/update', [ContentAdsController::class, 'updateStep'])
                    ->name('contentAds.updateStep')
                    ->where('step', '[1-3]');
            });
    });