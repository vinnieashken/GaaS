<?php

use App\Http\Middleware\AppKey;
use App\Http\Middleware\ProfileAuth;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
        $middleware->alias([
            'permission' => Spatie\Permission\Middleware\PermissionMiddleware::class,
            'profile_auth' => ProfileAuth::class,
            'appkey' => AppKey::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'mpesa/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
