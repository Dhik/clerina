<?php

use Illuminate\Support\Facades\Route;
use App\Domain\KPIEmployee\Controllers\KPIEmployeeController;

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
        Route::prefix('kpi-employee')
            ->group(function () {

                Route::get('/', [KPIEmployeeController::class, 'index'])->name('kPIEmployee.index');
                Route::get('/data', [KPIEmployeeController::class, 'data'])->name('kPIEmployee.data');
                Route::get('/staff-kpi-data', [KPIEmployeeController::class, 'getStaffKpiData'])->name('kPIEmployee.staffKpiData');
                Route::get('/create', [KPIEmployeeController::class, 'create'])->name('kPIEmployee.create');
                Route::post('/', [KPIEmployeeController::class, 'store'])->name('kPIEmployee.store');
                Route::get('/{employee}/detail', [KPIEmployeeController::class, 'show'])->name('kPIEmployee.show');
                Route::get('/{employee}/kpi-data', [KPIEmployeeController::class, 'getKpiData'])->name('kPIEmployee.kpiData');
                Route::get('/{kPIEmployee}/edit', [KPIEmployeeController::class, 'edit'])->name('kPIEmployee.edit');
                Route::put('/{kPIEmployee}', [KPIEmployeeController::class, 'update'])->name('kPIEmployee.update');
                Route::get('/{kPIEmployee}/input-actual', [KPIEmployeeController::class, 'inputActual'])->name('kPIEmployee.inputActual');
                Route::put('/{kPIEmployee}/update-actual', [KPIEmployeeController::class, 'updateActual'])->name('kPIEmployee.updateActual');
                Route::delete('/{kPIEmployee}', [KPIEmployeeController::class, 'destroy'])->name('kPIEmployee.destroy');
            });
    });