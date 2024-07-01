<?php

use App\Domain\Marketing\Controllers\MarketingCategoryController;
use App\Domain\Marketing\Controllers\MarketingController;
use App\Domain\Marketing\Controllers\MarketingSubCategoryController;
use App\Domain\Marketing\Controllers\SocialMediaController;
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
        Route::prefix('marketing')
            ->group(function () {

                Route::get('/', [MarketingController::class, 'index'])->name('marketing.index');
                Route::get('/get', [MarketingController::class, 'get'])->name('marketing.get');
                Route::get('/recap', [MarketingController::class, 'getMarketingRecap'])
                    ->name('marketing.get-marketing-recap');
                Route::post('/branding', [MarketingController::class, 'storeBrandingData'])
                    ->name('marketing.branding.store');
                Route::post('/marketing', [MarketingController::class, 'storeMarketingData'])
                    ->name('marketing.marketing.store');
                Route::put('/branding/{marketing}', [MarketingController::class, 'updateBrandingData'])
                    ->name('marketing.branding.update');
                Route::put('/marketing/{marketing}', [MarketingController::class, 'updateMarketingData'])
                    ->name('marketing.marketing.update');

                Route::get('/exportTemplate', [MarketingController::class, 'downloadTemplate'])
                    ->name('marketing.download-template');
                Route::post('/export', [MarketingController::class, 'export'])->name('marketing.export');
                Route::post('/import', [MarketingController::class, 'import'])->name('marketing.import');

                Route::delete('{marketing}', [MarketingController::class, 'destroy'])->name('marketing.destroy');
            });

        // Social Media
        Route::prefix('social-media')
            ->group(function () {
                Route::get('/', [SocialMediaController::class, 'index'])->name('socialMedia.index');
                Route::get('/get', [SocialMediaController::class, 'get'])->name('socialMedia.get');
                Route::post('/store', [SocialMediaController::class, 'store'])->name('socialMedia.store');
                Route::put('/update/{socialMedia}', [SocialMediaController::class, 'update'])
                    ->name('socialMedia.update');
                Route::delete('/destroy/{socialMedia}', [SocialMediaController::class, 'delete'])
                    ->name('socialMedia.destroy');
            });

        // Marketing category
        Route::prefix('marketing-category')
            ->group(function () {
                Route::get('/', [MarketingCategoryController::class, 'index'])
                    ->name('marketingCategories.index');
                Route::get('/get', [MarketingCategoryController::class, 'get'])
                    ->name('marketingCategories.get');
                Route::post('/store', [MarketingCategoryController::class, 'store'])
                    ->name('marketingCategories.store');
                Route::get('/{marketingCategory}', [MarketingCategoryController::class, 'show'])
                    ->name('marketingCategories.show');
                Route::put('/update/{marketingCategory}', [MarketingCategoryController::class, 'update'])
                    ->name('marketingCategories.update');
                Route::delete('/destroy/{marketingCategory}', [MarketingCategoryController::class, 'delete'])
                    ->name('marketingCategories.destroy');
            });

        // Marketing sub category
        Route::prefix('marketing-sub-category')
            ->group(function () {
                Route::get('/get/{marketingCategoryId}', [MarketingSubCategoryController::class, 'get'])
                    ->name('marketingSubCategories.get');
                Route::post('/store', [MarketingSubCategoryController::class, 'store'])
                    ->name('marketingSubCategories.store');
                Route::put('/update/{marketingSubCategory}', [MarketingSubCategoryController::class, 'update'])
                    ->name('marketingSubCategories.update');
                Route::delete('/destroy/{marketingSubCategory}', [MarketingSubCategoryController::class, 'delete'])
                    ->name('marketingSubCategories.destroy');
            });
    });
