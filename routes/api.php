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

// 需 token 認證的 route
Route::middleware(['cors', 'auth.jwt'])->group(function () {
    Route::namespace('Api')->prefix('member')->group(function () {
        //取所有會員
        Route::get('all', 'MemberController@allMember');
        //會員資料查詢
        Route::get('query', 'MemberController@queryMember');
        //單一會員資料查詢
        Route::get('single/{id}', 'MemberController@singleMember');
        //更新會員資料
        Route::post('update/{id}', 'MemberController@updateMember');
        //刪除會員
        Route::post('delete/{id}', 'MemberController@deleteMember');
        //會員密碼修改
        Route::post('password/{id}', 'MemberController@changePassword');
        //發送-Email驗證信
        Route::post('sendValidateEmail', 'MemberController@sendValidateEmail');
        //更新會員憑證
        Route::post('token', 'MemberController@refreshToken');
    });

    Route::namespace('Api')->prefix('newsletter')->group(function () {
        //取得所有電子報名單資料
        Route::get('all', 'NewsletterController@all');
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
        Route::post('validate/email', 'MemberController@validateEmail');
        //發送忘記密碼信
        Route::post('sendForgetPassword', 'MemberController@sendForgetPassword');
        //驗證-重設密碼
        Route::post('resetPassword', 'MemberController@resetPassword');
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
        //子分類搜尋（商品）
        Route::get('subcategory/{subcategoryId}', 'LayoutController@subcategory');
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
        Route::get('info/{source}', 'CheckoutController@info');
        // 設定物流方式
        Route::post('shipment', 'CheckoutController@shipment');
        // 確定結帳
        Route::post('confirm', 'CheckoutController@confirm');
        // 3D驗證
        Route::post('verify3d', 'CheckoutController@verify3d');
        // 取得3D驗證回傳資料
        Route::post('verifyResult', 'CheckoutController@verifyResult');
    });

    Route::prefix('coupon')->group(function () {
        Route::post('add', 'SalesRuleController@addCoupon');
        Route::post('remove', 'SalesRuleController@deleteCoupon');
    });

    Route::prefix('order')->group(function () {
        Route::get('info',   'OrderController@info');
        Route::get('items/{itemId}', 'OrderController@items');
        Route::get('search', 'OrderController@search');
        Route::get('detail/{id}', 'OrderController@find');

    });

    Route::prefix('wishlist')->group(function () {
        Route::get('items',   'WishlistController@items');
        Route::post('add/{id}', 'WishlistController@add');
        Route::post('delete/{id}', 'WishlistController@delete');

    });

    Route::prefix('layout')->group(function () {
        Route::get('home',   'LayoutController@home');
        Route::get('ads',   'LayoutController@ads');
        Route::get('exploration',   'LayoutController@exploration');
        Route::get('customize',   'LayoutController@customize');
        Route::get('banner',   'LayoutController@banner');
//        Route::get('info',   'LayoutController@info');
        Route::get('category/{categoryId}', 'LayoutController@category');
        Route::get('menu', 'LayoutController@menu');
    });

    Route::prefix('notification')->group(function () {
        //手機註冊推播token
        Route::post('register', 'NotificationController@register');
        //後台發送推播訊息
        Route::post('send', 'NotificationController@send');
        //取所有訊息
        Route::get('all', 'NotificationController@allMessage');
        //訊息資料查詢
        Route::get('query/{id}', 'NotificationController@queryMessage');
    });

    Route::prefix('newsletter')->group(function () {
        //新增電子報名單
        Route::post('order', 'NewsletterController@orderNewsletter');
    });

    Route::prefix('ticket')->group(function () {
        //票券使用說明
        Route::get('help',   'MyTicketController@help');
        //票券列表
        Route::get('info/{status}', 'MyTicketController@info');
        //票券明細
        Route::get('detail/{id}', 'MyTicketController@detail');
    });

});
