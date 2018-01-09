<?php

Route::namespace('Web\Ipass')->group(function () {
	Route::get('login/{platform?}', 'MemberController@login');
	Route::post('memberCallback/{platform?}', 'MemberController@callback');
	//愛pass會員登出
    Route::post('logout/{platform?}', 'MemberController@logout');
});
