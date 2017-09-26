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
Route::middleware(['cors', 'auth.jwt'])->group(function () {
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
        Route::post('password/{id}', 'MemberController@changePassword');
        //發送Email驗證信
        Route::post('sendValidateEmail', 'MemberController@sendValidateEmail');
        //更新會員憑證
        Route::put('token', 'MemberController@refreshToken');
    });
});

Route::middleware('cors')->namespace('Api')->group(function () {

    Route::prefix('member')->group(function () {
        //新增會員
        Route::post('create', 'MemberController@createMember');
        //驗證-手機驗證碼
        Route::post('validate/cellphone/{id}', 'MemberController@validateCellphone');
        //發送手機驗證碼
        Route::post('sendValidPhoneCode', 'MemberController@sendValidPhoneCode');
        //確認Email是否已使用
        Route::post('checkEmail', 'MemberController@checkEmail');
        //註冊-更新會員資料
        Route::post('register/{id}', 'MemberController@registerMember');
        //新增會員憑證
        Route::post('token', 'MemberController@generateToken');
        //驗證-Email驗證碼
        Route::post('validate/email/{id}', 'MemberController@validateEmail');
    });

    Route::prefix('product')->group(function () {
        // 取得所有商品列表
        Route::get('all', 'ProductController@all');
        // 根據商品分類取得商品列表
        Route::get('tags', 'ProductController@tags');
        // 根據 id 取得商品明細
        Route::get('query/{id}', 'ProductController@query');
        // 商品搜尋
        Route::get('search', 'ProductController@search');
    });

    Route::prefix('cart')->group(function () {
        // 購物車簡易資訊
        Route::get('info', 'CartController@info');
        // 購物車詳細資訊
        Route::get('detail', 'CartController@detail');
        // 增加商品至購物車
        Route::post('add', 'CartController@add');
        // 更新購物車內商品
        Route::post('update', 'CartController@update');
        // 刪除購物車內商品
        Route::post('delete', 'CartController@delete');
    });

    Route::prefix('checkout')->group(function () {
        // 取得結帳資訊
        Route::get('info', 'CheckoutController@info');
        // 確定結帳
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

    Route::prefix('wishlist')->group(function () {
        Route::get('items',   'WishlistController@items');
        Route::post('add/{id}', 'WishlistController@add');
        Route::post('delete/{id}', 'WishlistController@delete');

    });
});
