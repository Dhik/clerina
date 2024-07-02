<?php

use App\Domain\User\Controllers\AuthController;
use App\Domain\User\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Domain\Employee\Controllers\AttendanceController;

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

Route::get('/login', [AuthController::class, 'login'])->name('auth.login');
Route::get('/absensi', [AuthController::class, 'absensi'])->name('auth.absensi');
Route::post('/login', [AuthController::class, 'loginVerify'])->name('auth.login-verify');
Route::post('/absensi-login', [AuthController::class, 'absensiLoginVerify'])->name('auth.absensi-login-verify');
Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

Route::prefix('admin')
    ->middleware('auth')
    ->group(function () {
        Route::get('dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

        Route::prefix('users')
            ->group(function () {
                Route::get('/get', [UserController::class, 'get'])->name('users.get');
                Route::get('/', [UserController::class, 'index'])->name('users.index');
                Route::get('/create', [UserController::class, 'create'])->name('users.create');
                Route::post('/', [UserController::class, 'store'])->name('users.store');
                Route::get('/{user}', [UserController::class, 'show'])->name('users.show');
                Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
                Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
                Route::delete('{user}', [UserController::class, 'destroy'])->name('users.destroy');
                Route::get('/{user}/reset-password', [UserController::class, 'resetPassword'])
                    ->name('users.editPasswordReset');
                Route::put('/{user}/reset-password', [UserController::class, 'updateResetPassword'])
                    ->name('users.updatePasswordReset');
            });

        Route::get('/profile', [UserController::class, 'viewProfile'])->name('user.profile');
        Route::get('/changeOwnPassword', [UserController::class, 'changePassword'])
            ->name('user.changePassword');
        Route::put('/changeOwnPassword', [UserController::class, 'postChangePassword'])
            ->name('user.postChangeOwnPassword');
    });
