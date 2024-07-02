<?php

use App\Domain\Funnel\Controllers\FunnelController;
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

        Route::prefix('funnel')
            ->group(function () {
                Route::prefix('input')
                    ->group(function () {
                        Route::get('/', [FunnelController::class, 'input'])->name('funnel.input');
                        Route::get('/get', [FunnelController::class, 'get'])->name('funnel.input.get');

                        Route::post('/tofu', [FunnelController::class, 'storeTofu'])->name('funnel.input.tofu');
                        Route::post('/mofu', [FunnelController::class, 'storeMofu'])->name('funnel.input.mofu');
                        Route::post('/bofu', [FunnelController::class, 'storeBofu'])->name('funnel.input.bofu');
                    });

                Route::prefix('recap')
                    ->group(function () {
                        Route::get('/', [FunnelController::class, 'recap'])->name('funnel.recap');
                        Route::get('/get', [FunnelController::class, 'getRecap'])->name('funnel.recap.get');
                    });

                Route::prefix('total')
                    ->group(function () {
                        Route::get('/', [FunnelController::class, 'total'])->name('funnel.total');
                        Route::get('/get', [FunnelController::class, 'getTotal'])->name('funnel.total.get');
                        Route::post('/screenshot/{funnelTotal}', [FunnelController::class, 'storeScreenshot'])->name('funnel.store-screenshot');
                        Route::get('/screenshot/{funnelTotal}', [FunnelController::class, 'getScreenshot'])->name('funnel.get-screenshot');
                    });

            });
    });
