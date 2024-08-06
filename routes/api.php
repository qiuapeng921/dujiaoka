<?php

use Illuminate\Support\Facades\Route;

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


Route::group(['namespace' => 'Home'], function () {
    Route::get('checkUpgrade/{type}', 'HomeController@checkUpgrade');

    Route::group(['prefix' => 'user'], function () {
        Route::post('login', 'AuthController@login');
        Route::post('register', 'AuthController@register');

        Route::group(['middleware' => ['user.auth']], function () {
            Route::post('operateBalance', 'UserController@operateBalance');
            Route::get('info', 'UserController@info');
        });
    });

    Route::group(['prefix' => 'goods'], function () {
        Route::get('list', 'GoodsController@list');
    });

    Route::group(['prefix' => 'pay'], function () {
        Route::get('ways', 'CreateOrderController@payWays');
        Route::post('url', 'CreateOrderController@pay');
    });

    Route::group(['middleware' => ['user.auth']], function () {
        Route::post('orderCreate', 'CreateOrderController@createOrder');
    });

});
