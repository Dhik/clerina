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

Route::prefix('product')
    //->middleware('auth')
    ->group(function () {

        Route::get('/', 'ProductController@index')->name('product.index');
        Route::get('/create', 'ProductController@create')->name('product.create');
        Route::post('/', 'ProductController@store')->name('product.store');
        Route::get('/{product}', 'ProductController@show')->name('product.show');
        Route::get('/{product}/edit', 'ProductController@edit')->name('product.edit');
        Route::put('/{product}', 'ProductController@update')->name('product.update');
        Route::delete('{product}', 'ProductController@destroy')->name('product.destroy');
    });
