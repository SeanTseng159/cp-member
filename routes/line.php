<?php

Route::namespace('Web\Line')->group(function () {
		Route::get('login/{platform?}', 'MemberController@login');
		Route::get('memberCallback', 'MemberController@callback')->name('line.memberCallback');
});
