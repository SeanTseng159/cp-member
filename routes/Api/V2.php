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
        // 立即購買
        Route::post('buyNow', 'CartController@buyNow')->middleware('verify.cart.buyNow');

        // 獨立賣場立即購買
        Route::post('buyNow/market', 'CartController@market')->middleware('verify.cart.buyNow.market');

        // 取立即購買 (購物車跟付款資訊)
        Route::get('buyNow/info', 'CartController@info')->middleware('verify.cart.buyNow.info');
    });
    // 餐車相關
    Route::prefix('diningCar')->group(function () {
        // 可使用禮物數、優惠卷 與 總和
        Route::get('tickets', 'DiningCarMemberController@tickets');
    });

    // 優惠折價倦
    Route::prefix('discount')->group(function () {
        // 可使用禮物數、優惠卷 與 總和
        Route::get('listCanUsed', 'MemberDiscountController@listCanUsed');
        // 直接購買的優惠券
        Route::get('listCanUsedByProdId', 'MemberDiscountController@listCanUsedByProdId');

        Route::post('getByCode', 'MemberDiscountController@getByCode');

        Route::get('list/{func}', 'MemberDiscountController@list');

    });

    // 優惠折價倦
    Route::prefix('CouponOnline')->group(function () {
        // 可使用禮物數、優惠卷 與 總和
        Route::get('listCouponOnlineCanUsed', 'MemberCouponOnlineController@listCouponOnlineCanUsed');

    });

    
});

// 不需 token 認證的 route
Route::middleware(['cors'])->namespace('V2')->group(function () {

    // 優惠折價倦
    Route::prefix('discount')->group(function () {
        // 可使用禮物數、優惠卷 與 總和
        Route::get('listByProd/{prodId}', 'MemberDiscountController@listByProd');
    });
});
