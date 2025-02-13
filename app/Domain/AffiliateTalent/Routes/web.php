<?php

use Illuminate\Support\Facades\Route;
use App\Domain\AffiliateTalent\Controllers\AffiliateTalentController;

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
        Route::prefix('affiliate')->group(function () {
            Route::get('/', [AffiliateTalentController::class, 'index'])->name('affiliate.index');
            Route::get('/create', [AffiliateTalentController::class, 'create'])->name('affiliate.create');
            Route::get('/get', [AffiliateTalentController::class, 'data'])->name('affiliate.data');
            Route::post('/', [AffiliateTalentController::class, 'store'])->name('affiliate.store');
            Route::get('/{affiliate}', [AffiliateTalentController::class, 'show'])->name('affiliate.show');
            Route::get('/{affiliate}/edit', [AffiliateTalentController::class, 'edit'])->name('affiliate.edit');
            Route::put('/{affiliate}', [AffiliateTalentController::class, 'update'])->name('affiliate.update');
            Route::delete('/{affiliate}', [AffiliateTalentController::class, 'destroy'])->name('affiliate.destroy');
        });
    });
