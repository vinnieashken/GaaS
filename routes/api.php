<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GatewaysController;
use App\Http\Controllers\OrdersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::group(['middleware' => ['api','appkey']], function () {

    Route::get('gateways/list', [GatewaysController::class, 'getGateways']);

    Route::post('auth/user/token',[AuthController::class, 'userToken']);
    Route::post('auth/client/token',[AuthController::class, 'clientToken']);
    Route::post('auth/refresh/token',[AuthController::class, 'refreshToken']);

    Route::group(['middleware' => 'profile_auth:api'], function () {
        Route::post('payment/initiate',[OrdersController::class,'paymentRequest']);
        Route::get('gateways',[GatewaysController::class,'getProfileGateways']);
    });
});
