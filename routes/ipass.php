<?php

Route::namespace('Web\Ipass')->group(function () {
	Route::get('login/{platform?}', 'MemberController@login');
	Route::post('memberCallback/{platform?}', 'MemberController@callback');
	//愛pass會員登出
    Route::get('logout/{platform?}', 'MemberController@logout');
});
