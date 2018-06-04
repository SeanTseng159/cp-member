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
Route::middleware('cors')->group(function () {
    Route::prefix('member')->group(function () {
        //新增會員
        Route::post('create', 'MemberController@createMember')
            ->middleware('verify.member.create');
        //驗證-手機驗證碼
        Route::post('validate/cellphone/{id}', 'MemberController@validateCellphone');
        //發送手機驗證碼
        Route::post('sendValidPhoneCode', 'MemberController@sendValidPhoneCode')
            ->middleware('verify.send.validPhoneCode');
        //確認Email是否已使用
        Route::post('checkEmail', 'MemberController@checkEmail');
        //註冊-更新會員資料
        Route::post('register/{id}', 'MemberController@registerMember');
        //會員登入取憑證
        Route::post('token', 'MemberController@generateToken');
        //驗證-Email驗證碼
        Route::post('validate/email', 'MemberController@validateEmail');
        //發送忘記密碼信
        Route::post('sendForgetPassword', 'MemberController@sendForgetPassword');
        //驗證-重設密碼
        Route::post('resetPassword', 'MemberController@resetPassword');
    });
});

Route::middleware('cors')->namespace('V1')->group(function () {
    Route::prefix('product')->group(function () {
        // 取得所有商品列表
        Route::get('all', 'ProductController@all');
        // 根據商品分類取得商品列表
        Route::get('tags', 'ProductController@tags');
        // 根據 id 取得商品明細
        Route::get('query/{id}', 'ProductController@query');
        // 根據 id 取得加購商品
        Route::get('purchase/{id}', 'ProductController@purchase');
        // 商品搜尋
        Route::get('search', 'ProductController@search');
        //主分類搜尋（商品）
        Route::get('category/{categoryId}', 'LayoutController@maincategory');
        //子分類搜尋（商品）
        Route::get('subcategory/{subcategoryId}', 'LayoutController@subcategory');

        Route::get('cache/clean/all', 'ProductController@cleanAllProductCache');

        Route::get('cache/clean/{id}', 'ProductController@cleanProductCache');
    });
});
