<?php

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

Route::prefix('talent')
    //->middleware('auth')
    ->group(function () {

        Route::get('/', 'TalentController@index')->name('talent.index');
        Route::get('/create', 'TalentController@create')->name('talent.create');
        Route::post('/', 'TalentController@store')->name('talent.store');
        Route::get('/{talent}', 'TalentController@show')->name('talent.show');
        Route::get('/{talent}/edit', 'TalentController@edit')->name('talent.edit');
        Route::put('/{talent}', 'TalentController@update')->name('talent.update');
        Route::delete('{talent}', 'TalentController@destroy')->name('talent.destroy');
    });
