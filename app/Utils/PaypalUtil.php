<?php

namespace App\Utils;

use App\Models\Order;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class PaypalUtil
{
    public $baseurl;
    public $client_id;
    public $client_secret;
    public function __construct($baseurl, $client_id, $client_secret)
    {
        $this->baseurl = $baseurl;
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
    }
    private function token()
    {
        $credentials = base64_encode($this->client_id.':'.$this->client_secret);
        $data = [
            'form_params' => [
                'grant_type' => 'client_credentials',
            ],
        ];

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic '.$credentials,
        ];

        $response = $this->request('POST',$this->baseurl . '/v1/oauth2/token' ,$data,$headers);

        return @$response->access_token;
    }

    public function createOrder($amount,$reference,$currency = 'USD')
    {
        $token = $this->token();

        $data = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => $currency,
                    'value' => $amount,
                ],
                "custom_id" => $reference,
                "invoice_id" => $reference
            ]],
            'application_context' => [
                'return_url' => redirect_url(route('paypal.fallback')),
                'cancel_url' => redirect_url(route('paypal.fallback')),
            ]
        ];

        $data = [
            'body' => json_encode($data),
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ];

        return $this->request('POST',$this->baseurl . '/v2/checkout/orders',$data,$headers);
    }

    public function captureOrder($orderId)
    {
        $token = $this->token();

        $url = $this->baseurl . "/v2/checkout/orders/{$orderId}/capture";
        $data = [];
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ];

        return $this->request('POST',$url,$data,$headers);
    }

    public function orderDetails($orderId)
    {
        $token = $this->token();

        $url = $this->baseurl . "/v2/checkout/orders/{$orderId}";
        $data = [];
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ];
        //status COMPLETED == paid
        return $this->request('GET',$url,$data,$headers);
    }

    private function request($method,$url,$payload,$headers=[])
    {
        $client = new Client(['headers' => $headers ,
            'verify'=> base_path('/cacert.pem'),'http_errors'=>false]);

        $response = null;
        try{
            $response = $client->request($method, $url, $payload);
        }catch(\Exception $e)
        {
            report($e);
        }
        if(is_null($response))
            return  null;

        $responseBody = @$response->getBody()->getContents();
        return (object)json_decode($responseBody);
    }
}
