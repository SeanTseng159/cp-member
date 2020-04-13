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
Route::middleware(['cors', 'auth.jwt'])->namespace('V3')->group(function () {
    //賣場增加多台購物車，怕影響到之前的使用改板為V3
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

});
