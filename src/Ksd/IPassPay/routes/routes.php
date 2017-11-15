<?php
/**
 * User: Lee
 * Date: 2017/10/27
 * Time: 下午2:20
 */

// 需 token 認證的 route
Route::group(['middleware' => 'ipasspay', 'prefix' => 'ipass', 'namespace' => 'Ksd\IPassPay\Http\Controllers'], function () {
    // 一卡通支付
    Route::post('pay', 'PayController@pay');

    Route::post('callback', 'PayController@callback');
});
