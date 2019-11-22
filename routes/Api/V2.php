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
Route::middleware(['cors', 'auth.jwt'])->namespace('V2')->group(function () {
    // 訂單相關
    Route::prefix('order')->group(function () {
        // 取得訂單列表
        Route::get('info', 'OrderController@info');

        // 取得單一訂單
        Route::get('detail/{orderNo}', 'OrderController@detail');
    });

    //禮物相關
    Route::prefix('gift')->group(function () {
        //禮物詳細
        Route::get('/{id}/type/{type}', 'MemberGiftController@show');

        // 產生禮物Qrcode
        Route::get('/qrcode/{giftId}/type/{type}', 'MemberGiftController@getQrcode');
    });

    Route::prefix('cart')->group(function () {
        // 取立即購買 (購物車跟付款資訊)
        Route::get('buyNow/info', 'CartController@info')->middleware('verify.cart.buyNow.info');
    });
});
