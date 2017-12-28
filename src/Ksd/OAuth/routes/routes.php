<?php
/**
 * User: Lee
 * Date: 2017/10/27
 * Time: 下午2:20
 */

// 需 token 認證的 route
Route::group(['middleware' => ['oauth'], 'prefix' => 'oauth', 'namespace' => 'Ksd\OAuth\Http\Controllers\OAuth'], function () {
    // create 一個client app
    Route::post('create', 'OAuthClientController@create');
    // auth_code 授權
    Route::post('authorize', 'OAuthClientController@authorize');
    // token
    Route::post('token', 'OAuthClientController@generateToken');

    // member login
    Route::group(['prefix' => 'member'], function () {
	    Route::get('login/{id}', 'OAuthController@login');
	    Route::post('login', 'OAuthController@loginHandle');
	    Route::get('authorize/{id}', 'OAuthController@authorize');
	    Route::post('authorize', 'OAuthController@authorizeHandle');
        Route::get('logout', 'OAuthController@logout');
	});
});
