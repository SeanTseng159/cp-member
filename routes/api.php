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

// 需 token 認證的 route
Route::middleware('auth.jwt')->group(function () {
    Route::namespace('Api')->prefix('member')->group(function () {
        //取所有會員
        Route::get('all', 'MemberController@allMember');
        //會員資料查詢
        Route::get('query', 'MemberController@queryMember');
        //更新會員資料
        Route::put('update/{id}', 'MemberController@updateMember');
        //刪除會員
        Route::delete('delete/{id}', 'MemberController@deleteMember');
        //會員密碼修改
        Route::get('password', 'MemberController@changePassword');
        //更新會員憑證
        Route::put('token', 'MemberController@refreshToken');
    });
});

Route::namespace('Api')->group(function () {

    Route::prefix('member')->group(function () {
        //新增會員
        Route::post('create', 'MemberController@createMember');
        //驗證-手機驗證碼
        Route::post('validate/cellphone', 'MemberController@validateCellphone');
        //註冊-更新會員資料
        Route::post('register/{id}', 'MemberController@registerMember');
        //新增會員憑證
        Route::post('token', 'MemberController@generateToken');
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
        Route::post('confirm', 'CheckoutController@confirm');
    });

    Route::prefix('coupon')->group(function () {
        Route::post('add', 'SalesRuleController@addCoupon');
        Route::post('remove', 'SalesRuleController@deleteCoupon');
    });

    Route::prefix('orders')->group(function () {
        Route::get('info',   'OrderController@info');
        Route::get('items/{itemId}', 'OrderController@items');
        Route::get('search', 'OrderController@search');

    });
});
