<?php

namespace App\Http\Middleware;

use App\Models\ProfileToken;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProfileAuth
{
    use \App\Traits\Response;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if (!$token) {
            return $this->error("Access token is required",['access token not supplied']);
        }

        $publicKey = file_get_contents(storage_path('oauth-public.key'));

        try{
            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));
        }catch (\Exception $exception){
            report($exception);
            return $this->error("Invalid or expired access token",['invalid access credentials']);
        }

        $decoded = @$decoded->{0};

        $tokenRecord = ProfileToken::where('access_token', $decoded)
            ->where('expires_at', '>', now())
            ->first();

        if (!$tokenRecord) {
            return $this->error("Invalid or expired access token",['invalid access credentials']);
        }

        $request->merge([
            'profile' => $tokenRecord->profile,
            'user' => $tokenRecord->profile->user,
        ]);

        return $next($request);
    }
}
