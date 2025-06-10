<?php

use Illuminate\Support\Facades\Route;
use App\Domain\ContentPlan\Controllers\ContentPlanController;

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
        Route::prefix('contentPlan')
            ->group(function () {

                // Standard CRUD routes
                Route::get('/', [ContentPlanController::class, 'index'])->name('contentPlan.index');
                Route::get('/create', [ContentPlanController::class, 'create'])->name('contentPlan.create');
                Route::post('/', [ContentPlanController::class, 'store'])->name('contentPlan.store');
                Route::get('/{contentPlan}', [ContentPlanController::class, 'show'])->name('contentPlan.show');
                Route::get('/{contentPlan}/edit', [ContentPlanController::class, 'edit'])->name('contentPlan.edit');
                Route::put('/{contentPlan}', [ContentPlanController::class, 'update'])->name('contentPlan.update');
                Route::delete('/{contentPlan}', [ContentPlanController::class, 'destroy'])->name('contentPlan.destroy');

                // Step-specific routes for workflow
                Route::get('/{contentPlan}/step/{step}', [ContentPlanController::class, 'editStep'])
                    ->name('contentPlan.editStep')
                    ->where('step', '[1-6]');
                
                Route::put('/{contentPlan}/step/{step}', [ContentPlanController::class, 'updateStep'])
                    ->name('contentPlan.updateStep')
                    ->where('step', '[1-6]');
            });
    });