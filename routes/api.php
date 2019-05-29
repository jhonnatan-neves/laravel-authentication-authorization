<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

Route::middleware('auth:api')->get('/user', 'Admin\DashboardController@index');

Route::prefix('public')->group(function () {
    Route::post('/signup', 'Admin\AuthController@signup');
    Route::post('/signin', 'Admin\AuthController@signin');
});