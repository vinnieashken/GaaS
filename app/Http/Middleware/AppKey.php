<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppKey
{
    use \App\Traits\Response;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $appkey = $request->header('appkey');
        if (empty($appkey)) {
           return  $this->error("App key is required", ["App key not found"]);
        }

        if ($appkey != env('API_KEY')) {
            return  $this->error("Invalid API supplied", ["App key mismatch"]);
        }

        return $next($request);
    }
}
