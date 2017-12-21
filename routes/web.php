<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::namespace('Web\Ipass')->group(function () {
	Route::prefix('ipass')->group(function () {
		Route::get('login', 'MemberController@login');
		Route::post('callback', 'MemberController@callback');
	});
});
