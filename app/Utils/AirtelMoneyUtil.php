<?php

namespace App\Utils;

use GuzzleHttp\Client;

class AirtelMoneyUtil
{
    public $client_id;//3ccb3313-542a-4c6b-8b93-abf3ed8546c9

    public $client_secret;//****************************
    public $apiurl;//https://openapiuat.airtel.africa
    public function __construct($apiurl,$client_id, $client_secret)
    {
        $this->apiurl = $apiurl;
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
    }

    public function token()
    {
        $url = $this->apiurl."/auth/oauth2/token";

        $data =[
            "grant_type"=> "client_credentials",
            "client_id"=> $this->client_id,
            "client_secret"=> $this->client_secret
        ];

        return $this->request('POST', $url, json_encode($data));
    }

    public function charge($reference,$amount,$currency,$country,$pin,$msisdn)
    {
        $token = $this->token();
        $url = $this->apiurl."/standard/v2/cashin/";

        $data =[
            'subscriber' =>[
                "msisdn"=> $msisdn,
            ],
            "transaction"=>[
                "amount"=> $amount,
                "id"=> $reference,
            ],
            "additional_info" =>[
                [
                    "key" => "remark",
                    "value" => $reference,
                ]
            ],
            "reference"=>$reference,
            "pin" => $pin,
        ];

        dd(json_encode($data));

        $headers = [
            "Authorization" => "Bearer ".$token,
            "X-Country" => $country,
            "X-Currency" => $currency,
            "X-Signature" => "",
            "X-Key" => "",
        ];

        return $this->request('POST', $url, json_encode($data), $headers);
    }

    private function request($method,$url,$payload,$headers=[])
    {
        $request_headers = [ 'Content-Type' => 'application/json','Accept' => 'application/json' ];
        $request_headers = array_merge($request_headers, $headers);
        $client = new Client(['headers' => $request_headers ,
            'verify'=> base_path('/cacert.pem'),'http_errors'=>false]);

        $response = null;
        try{
            $response = $client->request($method, $url, [
                'body' => $payload
            ]);
        }catch(\Exception $e)
        {
            report($e);
            dd($e->getMessage());
        }
        if(is_null($response))
            return  null;

        $responseBody = @$response->getBody()->getContents();
        return (object)json_decode($responseBody);
    }
}
