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
    Route::prefix('layout')->group(function () {
        // 取首頁資料
        Route::get('home', 'LayoutController@home');
        // 取選單
        Route::get('menu', 'LayoutController@menu');
        // 取子選單
        Route::get('menu/{id}', 'LayoutController@oneMenu');
        // 取熱門探索分類
        Route::get('category/{id}', 'LayoutController@category');
        // 取熱門探索分類下所有商品
        Route::get('category/{id}/products', 'LayoutController@categoryProducts');
        // 取子熱門探索分類下所有商品
        Route::get('subCategory/{id}/products', 'LayoutController@subCategoryProducts');
    });

    Route::prefix('cache')->group(function () {
        // 清所有快取
        Route::get('clean/all', 'CacheController@all');
        // 清首頁資料
        Route::get('clean/home', 'CacheController@home');
        // 清選單資料
        Route::get('clean/menu', 'CacheController@menu');
        // 清熱門探索分類
        Route::get('clean/category/{id}', 'CacheController@category');
        // 清熱門探索分類下所有商品
        Route::get('clean/category/{id}/products', 'CacheController@categoryProducts');
        // 清子熱門探索分類下所有商品
        Route::get('clean/subCategory/{id}/products', 'CacheController@subCategoryProducts');

        // 清除常見問題
        Route::get('clean/service/qa', 'CacheController@serviceQA');
    });

    Route::prefix('product')->group(function () {
        // 根據 id 取得商品明細
        Route::get('query/{id}', 'ProductController@query');
        // 根據 id 取得加購商品
        Route::get('purchase/{id}', 'ProductController@purchase');
        // 根據 id 取得組合項目商品
        Route::get('combo/{id}', 'ProductController@findComboItem');
        // 商品搜尋
        Route::get('search', 'ProductController@search')->middleware('verify.product.search');
    });

    Route::prefix('service')->group(function () {
        // 常見問題
        Route::get('qa', 'ServiceController@qa');
    });


    Route::prefix('linepay')->group(function () {
        Route::post('confirm/callback', 'LinePayController@confirmCallback');

        Route::get('confirm/failure', 'LinePayController@confirmCallbackFailure');
        Route::post('confirm/failure', 'LinePayController@confirmCallbackFailure');
    });
});

// 需 token 認證的 route
Route::middleware(['cors', 'auth.jwt'])->namespace('V1')->group(function () {
    Route::prefix('order')->group(function () {
        // 取得訂單列表
        Route::get('info', 'OrderController@info');
    });

    Route::prefix('cart')->group(function () {
        // 取得一次性購物車資訊並加入購物車(依來源)
        Route::get('one-off', 'CartController@oneOff');
    });
});
