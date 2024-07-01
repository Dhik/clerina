<?php

use App\Domain\Customer\Controllers\CustomerController;
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
    });
