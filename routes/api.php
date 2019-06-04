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

// email
Route::group([
    'prefix' => 'email'
], function () {
    Route::get('activate/{token}', 'Auth\AuthController@activate');
    Route::post('create', 'Email\PasswordResetController@create');
    Route::get('find/{token}', 'Email\PasswordResetController@find');
    Route::post('reset', 'Email\PasswordResetController@reset');
});
// auth
Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('signup', 'Auth\AuthController@signup');
    Route::post('signin', 'Auth\AuthController@signin');
});
// admin
Route::group([
    'middleware' => 'auth:api',
    'prefix' => 'admin'
], function () {
    Route::get('/users', 'Admin\DashboardController@index');
});