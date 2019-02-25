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
        // 清更多服務
        Route::get('clean/layout/apps', 'CacheController@apps');
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

    // 縣市列表
    Route::get('counties', 'AddressController@counties');

    // 餐車相關
    Route::prefix('diningCar')->group(function () {
        // 店家類型列表
        Route::get('categories/main', 'DiningCarController@mainCategories');

        // 取營業狀態列表
        Route::get('openStatus/list', 'DiningCarController@openStatusList');

        // 餐車列表
        Route::get('list', 'DiningCarController@list');

        // 餐車地圖
        Route::get('map', 'DiningCarController@map')->middleware('verify.diningCar.map');

        // 餐車詳細
        Route::get('detail/{id}', 'DiningCarController@detail');

        // 餐車動態消息
        Route::get('/{diningCarId}/blogs', 'DiningCarBlogController@list');

        // 餐車動態消息詳細
        Route::get('blog/{id}', 'DiningCarBlogController@detail');

        // 餐車菜單
        Route::get('/{diningCarId}/menus', 'DiningCarMenuController@list');

        // 餐車菜單詳細
        Route::get('menu/{id}', 'DiningCarMenuController@detail');
        
       
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
    
    
    Route::prefix('coupon')->group(function () {
        // 優惠卷列表
        Route::get('{modelType}/{modelSpecId}/list', 'CouponController@list');
    
        // 優惠卷詳細
        Route::get('/{id}', 'CouponController@detail');
        
    });
    
   
    
    
});

// 需 token 認證的 route
Route::middleware(['cors', 'auth.jwt'])->namespace('V1')->group(function () {
    // 收藏相關
    Route::prefix('wishlist')->group(function () {
        // 收藏列表
        Route::get('items', 'WishlistController@items');
    });

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

        // 立即購買
        Route::post('buyNow', 'CartController@buyNow')->middleware('verify.cart.buyNow');

        // 獨立賣場立即購買
        Route::post('buyNow/market', 'CartController@market')->middleware('verify.cart.buyNow.market');

        // 取立即購買 (購物車跟付款資訊)
        Route::get('buyNow/info', 'CartController@info')->middleware('verify.cart.buyNow.info');
    });

    // 結帳相關
    Route::prefix('checkout')->group(function () {
        // 付款資訊
        Route::get('info/{orderNo}', 'CheckoutController@info');

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

    // 餐車相關
    Route::prefix('diningCar')->group(function () {
        // 餐車加入收藏
        Route::post('{id}/favorite/add', 'MemberDiningCarController@add');

        // 餐車移除收藏
        Route::post('{id}/favorite/remove', 'MemberDiningCarController@remove');

        // 餐車收藏列表
        Route::get('favorites', 'MemberDiningCarController@favorites');

        // 餐車收藏分類
        Route::get('favorite/categories', 'MemberDiningCarController@categories');

        // 加入餐車會員
        Route::post('member/add', 'DiningCarMemberController@add');
    });

    // 會員相關
    Route::prefix('member')->group(function () {
        // 我的會員卡
        Route::get('diningCars', 'DiningCarMemberController@diningCars');
    });
    
    //coupon 優惠卷相關
    Route::prefix('coupon')->group(function () {
        // coupon加入收藏
        Route::post('{couponId}/favorite/add', 'MemberCouponController@addFavorite');
        
        // coupon移除收藏
        Route::post('{couponId}/favorite/remove', 'MemberCouponController@removeFavorite');
        
        // coupon 收藏列表
        Route::get('favorite/list', 'MemberCouponController@list');
    
    
        // 優惠卷核銷
        Route::post('/', 'MemberCouponController@use');
        
    });
    
    //禮物相關
    Route::prefix('gift')->group(function () {
        
        // 我的禮物列表
        Route::get('/list', 'MemberGiftController@list');
    
        //禮物詳細
        Route::get('/{id}', 'MemberGiftController@show');
        
        // 產生禮物Qrcode
        Route::get('/qrcode/{id}', 'MemberGiftController@getQrcode');
        
    });
});
