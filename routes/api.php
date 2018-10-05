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
Route::get('test/regex', 'TestController@regex');
// 需 管理者權限的token 認證的 route
Route::middleware(['cors', 'admin.jwt'])->namespace('Api')->group(function () {
    Route::prefix('oauth/member')->group(function () {
        //單一會員資料查詢
        Route::get('single/{id}', 'MemberController@singleMember');
    });

    Route::prefix('member')->group(function () {
        //取所有會員
        Route::get('all', 'MemberController@allMember');
        //會員資料查詢
        Route::get('query', 'MemberController@queryMember');
        //更新會員資料
        Route::post('update/{id}', 'MemberController@updateMember')
            ->middleware('verify.member.update.data');
        //刪除會員
        Route::post('delete/{id}', 'MemberController@deleteMember');
    });

    Route::prefix('newsletter')->group(function () {
        //取得所有電子報名單資料
        Route::get('all', 'NewsletterController@all');
    });

    Route::prefix('notification')->group(function () {
        //後台發送推播訊息
        Route::post('send', 'NotificationController@send');
        //訊息資料查詢
        Route::get('query/{id}', 'NotificationController@queryMessage');
    });

    Route::prefix('ipasspay')->group(function () {
        //退款
        Route::post('refund', 'IpassPayController@refund');
        //交易結果查詢
        Route::post('result', 'IpassPayController@result');
    });

    Route::prefix('mail')->group(function () {
        //發送繳款完成通知信
        Route::post('paymentComplete', 'MailController@paymentComplete');
    });

});

// 需 token 認證的 route
Route::middleware(['cors', 'auth.jwt'])->namespace('Api')->group(function () {
    Route::prefix('member')->group(function () {
        //單一會員資料查詢
        Route::get('single/{id}', 'MemberController@singleMember');
        //更新會員資料
        Route::post('update/{id}', 'MemberController@updateMember')
            ->middleware('verify.member.update.data');
        //會員密碼修改
        Route::post('password/{id}', 'MemberController@changePassword');
        //發送-Email驗證信
        Route::post('sendValidateEmail', 'MemberController@sendValidateEmail');
        //更新會員憑證
        Route::post('refreshToken', 'MemberController@refreshToken');

        //第三方登入驗證token
        Route::post('oauth/login', 'MemberController@oauthLogin');
    });

    Route::prefix('newsletter')->group(function () {
        //取得所有電子報名單資料
        Route::get('all', 'NewsletterController@all');
    });

    Route::prefix('cart')->group(function () {
        // 購物車簡易資訊
        Route::get('info', 'CartController@info');
        // 購物車詳細資訊
        Route::get('detail', 'CartController@detail');
        // 購物車詳細資訊(依來源)
        Route::get('mine', 'CartController@mine');
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
        Route::post('shipment', 'CheckoutController@shipment')
            ->middleware('verify.checkout.shipment');
        // 確定結帳 (回傳訂單號，非信用卡)
        Route::post('confirm', 'CheckoutController@confirm');
    });

    Route::prefix('order')->group(function () {
        Route::get('info',   'OrderController@info');
        Route::get('items/{itemId}', 'OrderController@items');
        Route::get('search', 'OrderController@search');
        Route::get('detail/{id}', 'OrderController@find');
        Route::post('writeoff', 'OrderController@writeoff');
        Route::post('update', 'OrderController@update');
    });

    Route::prefix('ticket')->group(function () {
        //票券列表
        Route::get('info/{status}', 'MyTicketController@info');
        //票券明細
        Route::get('detail/{id}', 'MyTicketController@detail');
        //票券使用紀錄
        Route::get('record/{id}', 'MyTicketController@record');
        // 票券轉贈
        Route::post('gift', 'MyTicketController@gift');
        // 票券退還
        Route::post('refund', 'MyTicketController@refund');
        // 隱藏票券
        Route::post('hide', 'MyTicketController@hide');
    });

    Route::prefix('coupon')->group(function () {
        Route::post('add', 'SalesRuleController@addCoupon');
        Route::post('remove', 'SalesRuleController@deleteCoupon');
    });

    Route::prefix('wishlist')->group(function () {
        Route::get('items',   'WishlistController@items');
        Route::post('add', 'WishlistController@add');
        Route::post('delete', 'WishlistController@delete');

    });

    Route::prefix('checkout')->group(function () {
        // 3D驗證
        Route::post('verify3d', 'CheckoutController@verify3d');
        // 取得3D驗證回傳資料
        Route::post('verifyResult', 'CheckoutController@verifyResult');
        // 信用卡送金流(藍新)
        Route::post('creditCard', 'CheckoutController@creditCard');
        // 信用卡送金流(台新)
        Route::post('transmit', 'CheckoutController@transmit');

        // 更新Linepay
        Route::post('linepay/updateOrder', 'CheckoutController@linepayUpdateOrder');
    });

    Route::prefix('notification')->group(function () {
        //手機註冊推播token
        Route::post('register', 'NotificationController@register');
        //取所有訊息
        Route::get('all', 'NotificationController@allMessage');
    });

});

Route::middleware('cors')->namespace('Api')->group(function () {

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
        Route::post('token', 'MemberController@generateToken')->middleware('verify.member.login');
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

    // 處理magento相關
    Route::prefix('magento')->group(function () {
        // 取得magento所有商品
        Route::get('products', 'MagentoProductController@all');
        Route::get('products/{id}', 'MagentoProductController@find');
        Route::post('query', 'MagentoProductController@query');
        Route::get('update/product/{id}', 'MagentoProductController@update');
        // Route::get('syncAll', 'MagentoProductController@syncAll');
    });

    Route::prefix('layout')->group(function () {
        Route::get('home',   'LayoutController@home');
        Route::get('ads',   'LayoutController@ads');
        Route::get('exploration',   'LayoutController@exploration');
        Route::get('customize',   'LayoutController@customize');
        Route::get('banner',   'LayoutController@banner');
//        Route::get('info',   'LayoutController@info');
        Route::get('category/{categoryId}', 'LayoutController@category');
        Route::get('menu/{categoryId?}', 'LayoutController@menu');
        Route::get('reload',   'LayoutController@cleanCache');
        Route::get('clean/{id}', 'LayoutController@clean');
        Route::get('mainClean/{id}', 'LayoutController@mainClean');
        Route::get('subClean/{id}', 'LayoutController@subClean');
        Route::get('cache/clean/menu', 'LayoutController@cleanMenu');
    });

    Route::prefix('newsletter')->group(function () {
        //新增電子報名單
        Route::post('order', 'NewsletterController@orderNewsletter');
    });

    Route::prefix('order')->group(function () {
        Route::get('cache/clean/member/{id}', 'OrderController@cleanMemberOrders');
    });

    Route::prefix('tspg')->group(function () {
        //台新信用卡回傳
        Route::post('postBack', 'CheckoutController@postBack');
        Route::post('result', 'CheckoutController@result');
    });

    Route::prefix('service')->group(function () {
        //
        Route::get('qa', 'ServiceController@qa');
        Route::post('suggestion', 'ServiceController@suggestion');
    });

    Route::prefix('ticket')->group(function () {
        //票券物理主分類
        Route::get('catalogIcon',   'MyTicketController@catalogIcon');
        //票券使用說明
        Route::get('help',   'MyTicketController@help');
    });

    Route::prefix('ipasspay')->group(function () {
        //退款
        Route::post('payNotify', 'IpassPayController@payNotify');
    });

});
