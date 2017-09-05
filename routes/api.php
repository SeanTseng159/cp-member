<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::namespace('Api')->group(function () {

    Route::prefix('product')->group(function () {
        Route::get('all', 'ProductController@all');
        Route::get('tags', 'ProductController@tags');
        Route::get('query/{id}', 'ProductController@query');
    });

    Route::prefix('cart')->group(function () {
        Route::get('info', 'CartController@info');
        Route::get('detail', 'CartController@detail');
        Route::post('add', 'CartController@add');
        Route::post('update', 'CartController@update');
        Route::post('delete', 'CartController@delete');
    });
});