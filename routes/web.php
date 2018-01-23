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

Route::get('shopping_process', function () {
    return view('web.shopping_process');
});

Route::get('e-ticket', function () {
    return view('web.e-ticket');
});

Route::get('delivery', function () {
    return view('web.delivery');
});

Route::get('returns_and_refunds', function () {
    return view('web.returns_and_refunds');
});

Route::get('e-ticket_gift', function () {
    return view('web.e-ticket_gift');
});

Route::get('privacy', function () {
    return view('web.privacy');
});

Route::get('terms', function () {
    return view('web.terms');
});

Route::get('contact', function () {
    return view('web.contact');
});
