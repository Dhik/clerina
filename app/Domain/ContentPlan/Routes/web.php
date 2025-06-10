<?php

use Illuminate\Support\Facades\Route;
use App\Domain\ContentPlan\Controllers\ContentPlanController;

/*
|--------------------------------------------------------------------------
| API Routes for Content Plan
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware('auth')
    ->group(function () {
        Route::prefix('contentPlan')
            ->group(function () {

                // Standard CRUD routes
                Route::get('/', [ContentPlanController::class, 'index'])->name('contentPlan.index');
                Route::get('/create', [ContentPlanController::class, 'create'])->name('contentPlan.create');
                Route::get('/{contentPlan}', [ContentPlanController::class, 'show'])->name('contentPlan.show');
                Route::get('/{contentPlan}/edit', [ContentPlanController::class, 'edit'])->name('contentPlan.edit');

                // API Routes for data processing
                Route::get('/data', [ContentPlanController::class, 'data'])->name('contentPlan.data');
                Route::post('/store', [ContentPlanController::class, 'store'])->name('contentPlan.store');
                Route::put('/{contentPlan}/update', [ContentPlanController::class, 'update'])->name('contentPlan.update');
                Route::delete('/{contentPlan}/destroy', [ContentPlanController::class, 'destroy'])->name('contentPlan.destroy');
                
                Route::put('/{contentPlan}/step/{step}/update', [ContentPlanController::class, 'updateStep'])
                    ->name('contentPlan.updateStep')
                    ->where('step', '[1-6]');
                
                // Additional API endpoints
                Route::get('/{contentPlan}/details', [ContentPlanController::class, 'getDetails'])->name('contentPlan.details');
                Route::get('/status-counts', [ContentPlanController::class, 'getStatusCounts'])->name('contentPlan.statusCounts');
                
                // Step-specific routes for workflow
                Route::get('/{contentPlan}/step/{step}', [ContentPlanController::class, 'editStep'])
                    ->name('contentPlan.editStep')
                    ->where('step', '[1-6]');
            });
    });