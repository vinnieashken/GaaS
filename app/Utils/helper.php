<?php

use App\Models\Profile;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

if(!function_exists("setup_postgres")){
    function setup_postgres(){

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS citext');
            // Ensure the PostgreSQL extension for full-text search is enabled
            DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
            DB::statement('CREATE EXTENSION IF NOT EXISTS unaccent');
        }
    }
}

if(!function_exists("make_property")){
    function resolve_properties($property, $object) {

        if (!is_object($object)) {
            return null;
        }

        // If the property is not nested, return directly
        if (!str_contains($property, '.')) {
            return $object->$property ?? null;
        }

        $properties = explode('.', $property);
        $properties = array_filter($properties, fn($value) => !empty($value));
        // Handle nested properties
        $result = $object;
        foreach ($properties as $sub_property) {
            if (is_object($result) && isset($result->$sub_property)) {
                $result = $result->$sub_property;
            } else {
                return null; // Stop if any property in the chain is missing
            }
        }

        return $result;
    }
}

if(!function_exists("default_meta")){
    function default_meta() {
        return [
            'current_page' => 1,
            'per_page'     => 1,
            'total'        => 1,
            'total_pages'  => 1,
            'from'         => 1,
            'to'           => 1,
        ];
    }
}

if(!function_exists("resolve_redirect_url"))
{
    function resolve_redirect($order) {
        switch ($order->gateway->provider) {
            case 'dpo': return route('dpo.checkout',$order);
            default: return null;
        }
    }
}

if (!function_exists('redirect_url')) {
    function redirect_url(string $url): string
    {
        $host = request()->getHost();

        if (in_array($host, ['localhost', '127.0.0.1'])) {
            return 'https://webhook.site/f50d2027-9f5b-4832-be1e-8d34e2ded537';
        }

        return $url;
    }
}

if(!function_exists("webhook_payload"))
{
    function webhook_payload($order)
    {
        $data = [
            'status' => $order->status,
            'message' => $order->provider_final_response,
            'payment_request' => [
                'payment_request_identifier' => $order->uuid,
                'invoice_number' => $order->invoice_number,
                'reference' => $order->identifier,
                'amount' => $order->amount,
                'currency' => $order->currency,
            ],
            'paid' => (bool)$order->paid,
            'receipt' => $order->receipt,
            'transaction_date' => $order->created_at->format('d-m-Y H:i:s'),
        ];

        return $data;
    }
}

if(!function_exists("generate_tokens"))
{
    function generate_tokens(Profile $profile)
    {
        $privateKey = file_get_contents(storage_path('oauth-private.key'));
        $accessToken = Str::random(64);
        $jwtAccessToken = JWT::encode([$accessToken], $privateKey, 'RS256');
        $refreshToken = Str::random(64);
        $jwtRefreshToken = JWT::encode([$refreshToken], $privateKey, 'RS256');
        $seconds = (int)env('APP_TOKEN_EXPIRY',1800);
        $expiresAt = now()->addSeconds($seconds);

        $profile->tokens()->create([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_at' => $expiresAt,
        ]);

        return [
            'access_token' => $jwtAccessToken,
            'refresh_token' => $jwtRefreshToken,
            'expires_at' => $expiresAt,
        ];
    }
}

if(!function_exists("isXmlString"))
{
    function isXmlString(string $input): bool
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($input);
        if ($xml === false) {
            libxml_clear_errors();
            return false;
        }
        return true;
    }
}
