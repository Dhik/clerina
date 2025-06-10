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

                // PUT THESE API ROUTES FIRST (before the /{contentPlan} routes)
                Route::get('/data', [ContentPlanController::class, 'data'])->name('contentPlan.data');
                Route::get('/status-counts', [ContentPlanController::class, 'getStatusCounts'])->name('contentPlan.statusCounts');
                Route::post('/store', [ContentPlanController::class, 'store'])->name('contentPlan.store');

                // Standard CRUD routes (THESE COME AFTER)
                Route::get('/', [ContentPlanController::class, 'index'])->name('contentPlan.index');
                Route::get('/create', [ContentPlanController::class, 'create'])->name('contentPlan.create');
                Route::get('/{contentPlan}', [ContentPlanController::class, 'show'])->name('contentPlan.show');
                Route::get('/{contentPlan}/edit', [ContentPlanController::class, 'edit'])->name('contentPlan.edit');
                Route::put('/{contentPlan}/update', [ContentPlanController::class, 'update'])->name('contentPlan.update');
                Route::delete('/{contentPlan}/destroy', [ContentPlanController::class, 'destroy'])->name('contentPlan.destroy');
                Route::get('/{contentPlan}/details', [ContentPlanController::class, 'getDetails'])->name('contentPlan.details');

                // Step-specific routes
                Route::get('/{contentPlan}/step/{step}', [ContentPlanController::class, 'editStep'])
                    ->name('contentPlan.editStep')
                    ->where('step', '[1-6]');
                
                Route::put('/{contentPlan}/step/{step}/update', [ContentPlanController::class, 'updateStep'])
                    ->name('contentPlan.updateStep')
                    ->where('step', '[1-6]');
            });
    });