<?php

use App\Domain\Tenant\Controllers\TenantController;
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

        // Tenant
        Route::prefix('tenant')
            ->group(function () {
                Route::get('/change/{tenantId}', [TenantController::class, 'changeTenant'])->name('tenant.change');

                Route::get('/', [TenantController::class, 'index'])->name('tenant.index');
                Route::get('/get', [TenantController::class, 'get'])->name('tenant.get');
                Route::post('/store', [TenantController::class, 'store'])->name('tenant.store');
                Route::put('/update/{tenant}', [TenantController::class, 'update'])
                    ->name('tenant.update');
                Route::delete('/destroy/{tenant}', [TenantController::class, 'destroy'])
                    ->name('tenant.destroy');
            });
    });
