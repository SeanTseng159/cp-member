<?php

Route::namespace('Web\Ipass')->group(function () {
	Route::get('login/{platform?}', 'MemberController@login');
	Route::post('memberCallback/{platform?}', 'MemberController@callback');
});
