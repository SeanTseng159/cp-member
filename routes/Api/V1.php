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

Route::middleware('cors')->namespace('V1')->group(function () {
    // 版為商品相關
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

        // 取供應商相關商品
        Route::get('supplier/{supplierId}/products', 'LayoutController@supplier')->name('v1.layout.supplier');

        // 取其他內部服務app
        Route::get('apps', 'LayoutAppController@all');

        Route::get('supplier/{supplierId}/products', 'LayoutController@supplier')->name('v1.layout.supplier');
    });

    // 快取相關
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

        // 清更多服務
        Route::get('clean/layout/apps', 'CacheController@apps');

        // 清除常見問題
        Route::get('clean/service/qa', 'CacheController@serviceQA');

        // 清付款方式
        Route::get('clean/checkout/paymentMethod', 'CacheController@paymentMethod');
    });

    // 商品相關
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

    // linepay相關
    Route::prefix('linepay')->group(function () {
        Route::post('confirm/callback', 'LinePayController@confirmCallback');

        Route::get('confirm/failure', 'LinePayController@confirmCallbackFailure');
        Route::post('confirm/failure', 'LinePayController@confirmCallbackFailure');
        Route::get('map/stores', 'LinePayMapController@stores');
    });

    // 其他
    Route::prefix('service')->group(function () {
        // 常見問題
        Route::get('qa', 'ServiceController@qa');
    });

    // 行銷活動相關
    Route::prefix('activity')->group(function () {
        // 獨立賣場
        Route::get('market/{id}', 'MarketController@find');
    });
});

// 需 token 認證的 route
Route::middleware(['cors', 'auth.jwt'])->namespace('V1')->group(function () {
    // 訂單相關
    Route::prefix('order')->group(function () {
        // 取得訂單列表
        Route::get('info', 'OrderController@info');

        // 取得單一訂單
        Route::get('detail/{orderNo}', 'OrderController@detail');

        // 搜尋訂單
        Route::get('search', 'OrderController@search');
    });

    // 購物車相關
    Route::prefix('cart')->group(function () {
        // 取得一次性購物車資訊並加入購物車(依來源) (magento)
        Route::get('one-off', 'CartController@oneOff');
    });

    // 結帳相關
    Route::prefix('checkout')->group(function () {
        // 立即購買
        Route::post('buyNow', 'CheckoutController@buyNow')->middleware('verify.checkout.buyNow');
        // 取立即購買 (購物車跟付款資訊)
        Route::get('buyNow/info', 'CheckoutController@info');

        // 獨立賣場立即購買
        Route::post('buyNow/market', 'CheckoutController@market');

        // 結帳
        Route::post('payment', 'CheckoutController@payment')->middleware('verify.checkout.payment');
        // 重新結帳
        Route::post('payment/repay/{orderNo}', 'CheckoutController@repay');
    });

    // 票券相關
    Route::prefix('ticket')->group(function () {
        // 票券列表
        Route::get('list/{status}', 'TicketController@all');
    });
});
