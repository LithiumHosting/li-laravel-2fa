<?php

Route::get('login', [
    'as'   => 'login',
    'uses' => 'Auth\AuthController@getLogin',
]);

Route::post('login', [
    'as'   => 'login',
    'uses' => 'Auth\AuthController@postLogin',
]);

Route::get('logout', [
    'as'   => 'logout',
    'uses' => 'Auth\AuthController@getLogout',
]);

Route::get('otp', [
    'as'   => 'auth.otp.show',
    'uses' => 'Auth\AuthController@getOtp',
]);

Route::post('otp', [
    'as'   => 'auth.otp.post',
    'uses' => 'Auth\AuthController@postOtp',
]);
