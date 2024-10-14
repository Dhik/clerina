<?php

use Illuminate\Support\Facades\Route;
use App\Domain\Talent\Controllers\TalentController;
use App\Domain\Talent\Controllers\TalentContentController;
use App\Domain\Talent\Controllers\TalentPaymentController;

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
    Route::prefix('talent')
        ->group(function () {

            Route::get('/', [TalentController::class, 'index'])->name('talent.index');
            Route::get('/data', [TalentController::class, 'data'])->name('talent.data');
            Route::post('/import', [TalentController::class, 'import'])->name('talent.import');
            Route::get('/download-template', [TalentController::class, 'downloadTalentTemplate'])->name('talent.downloadTemplate');
            Route::get('/create', [TalentController::class, 'create'])->name('talent.create');
            Route::post('/', [TalentController::class, 'store'])->name('talent.store');
            Route::get('/{talent}', [TalentController::class, 'show'])->name('talent.show');
            Route::get('/{talent}/edit', [TalentController::class, 'edit'])->name('talent.edit');
            Route::put('/{talent}', [TalentController::class, 'update'])->name('talent.update');
            Route::delete('{talent}', [TalentController::class, 'destroy'])->name('talent.destroy');
        });
    Route::prefix('tlnt-content')
        ->group(function () {
            Route::get('/', [TalentContentController::class, 'index'])->name('talent_content.index');
            Route::get('/talents', [TalentContentController::class, 'getTalents'])->name('talent_content.get');
            Route::get('/{talentContent}/export-pdf', [TalentContentController::class, 'exportPDF'])->name('talent_content.exportPDF');
            Route::get('/pengajuan', [TalentContentController::class, 'exportPengajuan'])->name('talent_content.pengajuan');
            Route::get('/generate-docx', [TalentContentController::class, 'generateDocx'])->name('talent_content.spk');
            Route::get('/invoice', [TalentContentController::class, 'showInvoice'])->name('talentContents.showInvoice');
            Route::get('/today', [TalentContentController::class, 'getTodayTalentNames'])->name('talent_content.today');
            Route::get('/calendar', [TalentContentController::class, 'calendar'])->name('talent_content.calendar');
            Route::get('/count', [TalentContentController::class, 'countContent'])->name('talent_content.count');
            Route::get('/data', [TalentContentController::class, 'data'])->name('talent_content.data');
            Route::get('/create', [TalentContentController::class, 'create'])->name('talent_content.create');
            Route::post('/', [TalentContentController::class, 'store'])->name('talent_content.store');
            Route::get('/{talentContent}', [TalentContentController::class, 'show'])->name('talent_content.show');
            Route::get('/{talentContent}/edit', [TalentContentController::class, 'edit'])->name('talent_content.edit');
            Route::put('/{talentContent}', [TalentContentController::class, 'update'])->name('talent_content.update');
            Route::delete('{talentContent}', [TalentContentController::class, 'destroy'])->name('talent_content.destroy');
        });

    Route::prefix('talnt-payments')
        ->group(function () {
            Route::get('/', [TalentPaymentController::class, 'index'])->name('talent_payments.index');
            Route::get('/data', [TalentPaymentController::class, 'data'])->name('talent_payments.data');
            Route::post('/', [TalentPaymentController::class, 'store'])->name('talent_payments.store');
            Route::get('/{payment}', [TalentPaymentController::class, 'show'])->name('talent_payments.show');
            Route::get('/{payment}/edit', [TalentPaymentController::class, 'edit'])->name('talent_payments.edit');
            Route::put('/{payment}', [TalentPaymentController::class, 'update'])->name('talent_payments.update');
            Route::delete('/{payment}', [TalentPaymentController::class, 'destroy'])->name('talent_payments.destroy');
        });
});
