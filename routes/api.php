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

Route::middleware('auth.jwt')->group(function () {
    Route::prefix('member')->group(function () {
        Route::get('query', function () {
            return 'test';
        });
    });
});

Route::namespace('Api')->group(function () {

    Route::prefix('member')->group(function () {
        //新增會員
        Route::post('new', 'MemberController@createMember');
        //更新會員資料
        Route::put('update/{id}', 'MemberController@updateMember');
        //刪除會員
        Route::delete('delete/{id}', 'MemberController@deleteMember');
        //新增會員憑證
        Route::post('token', 'MemberController@generateToken');
        //更新會員憑證
        Route::put('token', 'MemberController@refreshToken');
    });

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

    Route::prefix('checkout')->group(function () {
        Route::get('info', 'CheckoutController@info');
//        Route::get('detail', 'CartController@detail');
        Route::post('confirm', 'CheckoutController@confirm');
//        Route::post('update', 'CartController@update');
//        Route::post('delete', 'CartController@delete');
    });
});
