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
    // 會員相關
    Route::prefix('member')->group(function () {
        // 餐車邀請註冊會員
        Route::post('register/invite', 'MemberController@registerInvite')->middleware('verify.member.registerInvite');
        // 檢查是否已註冊會員
        Route::post('register/check', 'MemberController@registerCheck')->middleware('verify.member.registerCheck');
        // 檢查是否已註冊會員
        Route::post('register/check2', 'MemberController@registerCheck2')->middleware('verify.member.registerCheck2');
    });

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

    // 手機解碼
    Route::post('mobile/decode', 'MobileController@decode');

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

        // 餐車&會員資料
        Route::get('{id}/member/info', 'DiningCarMemberController@info');

        // 餐車加入會員
        Route::post('member/invite', 'DiningCarMemberController@invite');

        // 餐車縮網址
        Route::get('shorterUrl/{id}', 'DiningCarController@shorterUrl');
    });


    // 店鋪相關
    Route::prefix('shop')->group(function () {

        // 列表
        Route::get('list', 'ShopController@list');

        // 地圖
        Route::get('map', 'ShopController@map')->middleware('verify.diningCar.map');

        // 詳細
        Route::get('detail/{id}', 'ShopController@detail');

        //服務列表
        Route::get('service/list', 'ShopController@servicelist');

        //候位相關======
        Route::get('{id}/waiting', 'ShopWaitingController@info'); //店鋪資訊
        Route::post('{id}/waiting', 'ShopWaitingController@create'); //新增候位
        Route::get('{id}/waiting/{waitingId}', 'ShopWaitingController@get');//取得候位資訊

        Route::post('waiting/{code}', 'ShopWaitingController@deleteByCode');//取消候位
        Route::get('waiting/{code}', 'ShopWaitingController@getByCode');//取得候位資訊
        Route::get('waiting/member/list', 'ShopWaitingController@memberList')->middleware('auth.jwt');//我的候位資訊

        //訂位相關 ====
        //店鋪訂位取得人數及注意事
        Route::get('{id}/booking/people', 'ShopBookingController@maxpeople');
        //取得店舖可訂位日期
        Route::get('{id}/booking/date', 'ShopBookingController@findBookingCanDate');
        //完成訂位
        Route::post('{id}/booking/finished', 'ShopBookingController@finishedBooking');
        //取得單一訂單資訊
        Route::get('{shopId}/booking/{id}', 'ShopBookingController@getOenDetailInfo');
        Route::get('booking/{code}', 'ShopBookingController@get');
        //訂位短網址解碼
        Route::get('booking/getfromcode/{code}', 'ShopBookingController@get');
        //取消訂位
        Route::post('{shopId}/booking/{code}', 'ShopBookingController@delete');
        //已訂位列表
        Route::get('booking/member/list', 'ShopBookingController@memberList')->middleware('auth.jwt');

        //問卷相關
        Route::post('{id}/questionnaire/{questionId}', 'ShopQuestionController@create')->middleware('auth.jwt');
        Route::get('{id}/questionnaire', 'ShopQuestionController@get')->middleware('auth.jwt');

        //點餐相關
        Route::post('{shopId}/menuOrder', 'MenuOrderController@create');
        Route::get('menuOrder/{code}', 'MenuOrderController@detail');
        Route::POST('menuOrder/{code}', 'MenuOrderController@cancel');
        Route::get('menuOrder/qrcode/{orderId}', 'MenuOrderController@getQrCode')->middleware('auth.jwt');
        Route::get('menuOrder/member/list', 'MenuOrderController@memberList')->middleware('auth.jwt');



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

        // 合作廠商申請
        Route::post('partner/join', 'ServiceController@partnerJoin')->middleware('verify.partner.join');
    });

    // 行銷活動相關
    Route::prefix('activity')->group(function () {
        // 獨立賣場
        Route::get('market/{id}', 'MarketController@find');
    });

    //優惠卷相關
    Route::prefix('coupon')->group(function () {
        // 優惠卷列表
        Route::get('{modelType}/{modelSpecId}/list', 'CouponController@list');

        // 優惠卷詳細
        Route::get('/{id}', 'CouponController@detail');

        // 禮物清單
        Route::get('{modelType}/{modelSpecId}/gifts/', 'CouponController@gift_list');

    });

    //禮物、點數相關
    Route::prefix('gift')->group(function () {
        // 禮物列表
        Route::get('{modelType}/{modelSpecId}/list', 'GiftController@list');
    });


    //avr相關
    Route::prefix('avr')->group(function () {
        Route::prefix('activity')->group(function () {
            //活動列表
            Route::get('list', 'AVRActivityController@list');

            //活動明細
            Route::get('/{activityId}', 'AVRActivityController@detail');

            //任務列表
            Route::get('/{activityId}/order/{orderId}/missions', 'AVRActivityController@missionList');

            //任務明細
            Route::get('/order/{orderId}/mission/{missionId}', 'AVRActivityController@missionDetail');
        });

        Route::prefix('place')->group(function () {
            //地點列表
            Route::get('/', 'PlaceController@list');

            //地點detail
            Route::get('/{id}', 'PlaceController@detail');

            //地點icon
            Route::get('/icons/list', 'PlaceController@icons');

        });
    });

    //邀請碼相關
    Route::prefix('invitation')->group(function () {
        //邀請碼對應名字
        Route::post('memberName', 'MemberController@memberName');
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

    Route::prefix('DiscountCode')->group(function () {
        Route::post('add', 'SalesRuleController@addCoupon');
        Route::post('remove', 'SalesRuleController@deleteCoupon');
    });

    // 結帳相關
    Route::prefix('checkout')->group(function () {
        // 付款資訊
        Route::get('info/{orderNo}', 'CheckoutController@info');

        // 結帳
        Route::post('payment', 'CheckoutController@payment')->middleware('verify.checkout.payment');

        // 點餐單結帳
        Route::post('payment/menu_order/{menuOrderNo}', 'CheckoutController@menuPayment')->middleware('verify.checkout.payment.menu');

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

        // 可使用禮物數、優惠卷 與 總和
        Route::get('tickets', 'DiningCarMemberController@tickets');

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
        Route::get('/qrcode/{giftId}', 'MemberGiftController@getQrcode');


    });

    //點數相關
    Route::prefix('point')->group(function () {
        //兌換紀錄
        Route::get('{diningCarID}/list', 'DiningCarPointController@list');
        // 總點數
        Route::get('{diningCarID}', 'DiningCarPointController@total');

        //兌換點數
        Route::post('gift/{giftId}', 'DiningCarPointController@exchange');
    });

    //avr相關
    Route::prefix('avr')->group(function () {
        Route::prefix('activity')->group(function () {

            //任務結束
            Route::post('order/{orderId}/mission/{missionId}', 'AVRActivityController@missionEnd');

            //刪除user任務資訊
            Route::delete('order/{orderId}/mission/{missionId}', 'AVRActivityController@cancelMission');
        });
    });

    //邀請碼相關
    Route::prefix('invitation')->group(function () {
        //填寫邀請碼
        Route::post('input', 'MemberController@invitationInput');
        //邀請碼連結資訊
        Route::get('/info', 'MemberController@info');
    });

    //會員通知相關
    Route::prefix('memberNotic')->group(function () {
        //通知列表資訊
        Route::get('/info', 'MemberController@NoticInfo');
        //修改已讀狀態
        Route::post('read', 'MemberController@readStatusChange');
    });

});
