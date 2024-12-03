<?php

use Illuminate\Support\Facades\Route;
use App\Domain\Talent\Controllers\TalentController;
use App\Domain\Talent\Controllers\TalentContentController;
use App\Domain\Talent\Controllers\TalentPaymentController;
use App\Domain\Talent\Controllers\ApprovalController;

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

            Route::get('/{talent}/invoice', [TalentController::class, 'exportInvoice'])->name('talent.exportInvoice');
            Route::get('/{talent}/inv_data', [TalentController::class, 'exportInvData'])->name('talent.exportInvData');
            Route::get('/{talent}/spk', [TalentController::class, 'exportSPK'])->name('talent.spk');
            Route::get('/spk', [TalentController::class, 'showSPK'])->name('talent.spk_view');
            Route::get('/invoice', [TalentController::class, 'showInvoice'])->name('talent.showInvoice');

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
            Route::get('/export', [TalentContentController::class, 'export'])->name('talent_content.export');
            Route::get('/campaigns', [TalentContentController::class, 'getCampaigns'])->name('talent_content.getCampaigns');
            Route::post('/{id}/add-link', [TalentContentController::class, 'addLink'])->name('talent_content.addLink');

            Route::post('/{id}/refund', [TalentContentController::class, 'refund'])->name('talent_content.refund');
            Route::post('/{id}/unrefund', [TalentContentController::class, 'unrefund'])->name('talent_content.unrefund');

            Route::get('/today', [TalentContentController::class, 'getTodayTalentNames'])->name('talent_content.today');
            Route::get('/calendar', [TalentContentController::class, 'calendar'])->name('talent_content.calendar');
            Route::get('/count', [TalentContentController::class, 'countContent'])->name('talent_content.count');
            Route::get('/data', [TalentContentController::class, 'data'])->name('talent_content.data');

            Route::get('/products', [TalentContentController::class, 'getProducts'])->name('talent_content.get_products');
            Route::get('/line_chart_data', [TalentContentController::class, 'getLineChartData'])->name('talent_content.get_line_data');

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
            Route::get('/report', [TalentPaymentController::class, 'report'])->name('talent_payments.report');
            Route::get('/report_table', [TalentPaymentController::class, 'paymentReport'])->name('talent_payments.paymentReport');
            Route::get('/report_export', [TalentPaymentController::class, 'exportReport'])->name('talent_payments.export');
            Route::get('/export-talent-payments', [TalentPaymentController::class, 'exportPengajuanExcel'])->name('talent_payments.export_excel');
            Route::get('/card', [TalentPaymentController::class, 'getReportKPI'])->name('talent_payments.kpi');
            Route::get('/hutang_data', [TalentPaymentController::class, 'getHutangDatatable'])->name('talent_payments.hutangData');
            Route::get('/hutang_totals', [TalentPaymentController::class, 'calculateTotals'])->name('talent_payments.hutangTotals');
            Route::get('/pengajuan', [TalentPaymentController::class, 'exportPengajuan'])->name('talent_payments.pengajuan');
            Route::post('/', [TalentPaymentController::class, 'store'])->name('talent_payments.store');
            Route::get('/{payment}', [TalentPaymentController::class, 'show'])->name('talent_payments.show');
            Route::get('/{payment}/edit', [TalentPaymentController::class, 'edit'])->name('talent_payments.edit');
            Route::put('/{id}', [TalentPaymentController::class, 'update'])->name('talent_payments.update');
            Route::delete('/{payment}', [TalentPaymentController::class, 'destroy'])->name('talent_payments.destroy');
        });

    Route::prefix('approval')
        ->group(function () {
            Route::get('/', [ApprovalController::class, 'index'])->name('approval.index');
            Route::get('/data', [ApprovalController::class, 'data'])->name('approval.data');
            Route::post('/', [ApprovalController::class, 'store'])->name('approval.store');
            Route::get('/{approval}', [ApprovalController::class, 'show'])->name('approval.show');
            Route::get('/{approval}/edit', [ApprovalController::class, 'edit'])->name('approval.edit');
            Route::put('/{approval}', [ApprovalController::class, 'update'])->name('approval.update');
            Route::delete('{approval}', [ApprovalController::class, 'destroy'])->name('approval.destroy');
        });
});
