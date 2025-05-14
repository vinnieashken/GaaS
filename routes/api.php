<?php

use App\Http\Controllers\Api\V1\GatewaysController;
use App\Http\Controllers\Api\V1\OrdersController;
use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::group(['prefix'=> 'v1','middleware' => ['api','appkey']], function () {

    Route::any('gateways/list', [GatewaysController::class, 'getGateways']);

    Route::any('auth/user/token',[AuthController::class, 'userToken']);
    Route::any('auth/client/token',[AuthController::class, 'clientToken']);
    Route::any('auth/refresh/token',[AuthController::class, 'refreshToken']);

    Route::group(['middleware' => 'profile_auth:api'], function () {
        Route::any('payment/initiate',[OrdersController::class,'paymentRequest']);
        Route::any('gateways',[GatewaysController::class,'getProfileGateways']);
    });
});
