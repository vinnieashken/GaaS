<?php

use App\Http\Controllers\GatewayController;
use App\Http\Controllers\AppsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Providers\DpoController;
use App\Http\Controllers\Providers\MpesaController;
use App\Http\Controllers\Providers\PaypalController;
use App\Http\Controllers\TransactionsController;
use App\Jobs\NotifyWebhook;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

Route::get('login', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');
Route::get('password/request', [AuthController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('password/reset/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [AuthController::class, 'resetPassword'])->name('password.update');
Route::group(['middleware' => ['auth']], function () {
    Route::get('/',[HomeController::class,'index'])->name('home') ;

    Route::group(['prefix' => 'users'],function(){
        Route::get('/', [App\Http\Controllers\UsersController::class, 'index'])->name('users')->middleware('permission:view_users');
        Route::get('create', [App\Http\Controllers\UsersController::class, 'create'])->name('users.create')->middleware('permission:create_users');
        Route::post('store',[App\Http\Controllers\UsersController::class, 'store'])->name('users.store')->middleware('permission:create_users');
        Route::get('{user}/edit',[App\Http\Controllers\UsersController::class, 'edit'])->name('users.edit')->middleware('permission:edit_users');
        Route::patch('{user}/update',[App\Http\Controllers\UsersController::class, 'update'])->name('users.update')->middleware('permission:edit_users');
        Route::delete('{user}/destroy',[App\Http\Controllers\UsersController::class, 'destroy'])->name('users.destroy')->middleware('permission:delete_users');
    });

    Route::group(['prefix' => 'apps'],function(){
        Route::get("/",[AppsController::class,'index'])->name('apps');
        Route::get("create",[AppsController::class,'create'])->name('apps.create');
        Route::post("store",[AppsController::class,'store'])->name('apps.store');
        Route::get("{app}",[AppsController::class,'show'])->name('apps.show');
        Route::get("{app}/edit",[AppsController::class,'edit'])->name('apps.edit');
        Route::patch("{app}/update",[AppsController::class,'update'])->name('apps.update');
        Route::delete("{app}/destroy",[AppsController::class,'destroy'])->name('apps.destroy');
    });

    Route::group(['prefix' => 'currencies'],function(){
        Route::get("/",[CurrencyController::class,'index'])->name('currencies');
        Route::get("create",[CurrencyController::class,'create'])->name('currencies.create');
        Route::post("store",[CurrencyController::class,'store'])->name('currencies.store');
        //Route::get("{currency}",[CurrencyController::class,'show'])->name('currencies.show');
        Route::get("{currency}/edit",[CurrencyController::class,'edit'])->name('currencies.edit');
        Route::patch("{currency}/update",[CurrencyController::class,'update'])->name('currencies.update');
        Route::delete("{currency}/destroy",[CurrencyController::class,'destroy'])->name('currencies.destroy');
    });

    Route::group(['prefix' => 'gateways'],function(){
        Route::get("/",[GatewayController::class,'index'])->name('gateways');
        Route::get("create",[GatewayController::class,'create'])->name('gateways.create');
        Route::post("store",[GatewayController::class,'store'])->name('gateways.store');
        //Route::get("{gateway}",[GatewaysController::class,'show'])->name('gateways.show');
        Route::get("{gateway}/edit",[GatewayController::class,'edit'])->name('gateways.edit');
        Route::patch("{gateway}/update",[GatewayController::class,'update'])->name('gateways.update');
        Route::delete("{gateway}/destroy",[GatewayController::class,'destroy'])->name('gateways.destroy');
    });

    Route::group(['prefix' => 'transactions'],function(){
        Route::get("/",[TransactionsController::class,'index'])->name('transactions');
    });
});

Route::get('/welcome', function () {

    $order = Order::with(['gateway'])->find(27);
    $gateway = $order->gateway;
    $config = (object)$gateway->config;
    //NotifyWebhook::dispatch("https://example.com",webhook_payload($order),"gateway",$order->id);
    //$mpesa = new \App\Utils\MpesaUtil($config->base_url,$config->consumer_key,$config->consumer_secret,$config->passkey);
    //dd( $mpesa->stKPush($config->shortcode,$order->identifier,(int)$order->amount,$order->customer_phone,redirect_url(route('mpesa.stkcallback'))) );
    //$util = new \App\Utils\PaypalUtil($config->api_url,$config->client_id,$config->client_secret);
    //$res = $util->orderDetails($order->provider_code);
    $util = new \App\Utils\AirtelMoneyUtil("https://openapiuat.airtel.africa","3ccb3313-542a-4c6b-8b93-abf3ed8546c9","****************************");
    $res = $util->token();
    $res = $util->charge("ZXVFGT",20,"KES","KE","6694","707563017");
    dd($res);

    dd('done');
});

Route::get('dpo/{order}/checkout', [DpoController::class, 'checkout'])->name('dpo.checkout');
Route::get('dpo/callback', [DpoController::class, 'callback'])->name('dpo.callback');
Route::post('paypal/callback', [PayPalController::class, 'callback'])->name('paypal.callback');
Route::get('paypal/fallback', [PayPalController::class, 'callback'])->name('paypal.fallback');

Route::group(['prefix' => 'mpesa'],function(){
    Route::post('stk_callback', [MpesaController::class, 'stk_callback'])->name('mpesa.stkcallback');
    Route::post('c2b_callback', [MpesaController::class, 'c2b_callback'])->name('mpesa.c2bcallback');
    Route::post('validation_callback', [MpesaController::class, 'validation_callback'])->name('mpesa.validationcallback');
    Route::post('status_query_callback', [MpesaController::class, 'query_status_callback'])->name('mpesa.statusquerycallback');
    Route::post('queue_timeout',[MpesaController::class, 'queue_timeout'])->name('mpesa.queuetimeout');
    Route::post('registerUrl',[MpesaController::class, 'registerUrl'])->name('mpesa.registerurl');
});

Route::group(['prefix' => 'docs','middleware' => 'docs'],function(){
    Route::get("/",[DocumentationController::class,'index'])->name('docs');
    Route::get("/{version}",[DocumentationController::class,'documentation'])->name('docs.show');
    Route::get('swagger/{version}',[DocumentationController::class, 'swagger'])->name('docs.swagger');
});
