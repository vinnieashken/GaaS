<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Providers\DpoController;
use App\Http\Controllers\Providers\MpesaController;
use App\Http\Controllers\Providers\PaypalController;
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
});

Route::get('/welcome', function () {

    $order = Order::with(['gateway'])->find(27);
    $gateway = $order->gateway;
    $config = (object)$gateway->config;
    //NotifyWebhook::dispatch("https://example.com",webhook_payload($order),"gateway",$order->id);
    //$mpesa = new \App\Utils\MpesaUtil($config->base_url,$config->consumer_key,$config->consumer_secret,$config->passkey);
    //dd( $mpesa->stKPush($config->shortcode,$order->identifier,(int)$order->amount,$order->customer_phone,redirect_url(route('mpesa.stkcallback'))) );
    $util = new \App\Utils\PaypalUtil($config->api_url,$config->client_id,$config->client_secret);
    $res = $util->orderDetails($order->provider_code);
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
});

Route::group(['prefix' => 'docs'],function(){
    Route::get("/",[DocumentationController::class,'index'])->name('docs');
    Route::get('swagger',[DocumentationController::class, 'swagger'])->name('docs.swagger');
});
