<?php

namespace App\Utils;

use App\Models\Gateway;
use App\Models\Order;
use GuzzleHttp\Client;
use Mtownsend\XmlToArray\XmlToArray;
use Spatie\ArrayToXml\ArrayToXml;

class DpoUtil
{
    public $CompanyToken;
    public $apiUrl;
    public $paymentUrl;
    public $config;
    public $gateway;
    public function __construct(Gateway $gateway)
    {
        $this->gateway = $gateway;
        $config = $this->gateway->config;
        $this->config = (object)$config;
        $this->CompanyToken = $this->config->company_token;
        $this->apiUrl = $this->config->api_url;
        $this->paymentUrl = $this->config->redirect_url;
    }

    public function createToken($serviceID,$amount,$currency,$reference,$redirectURL,$backURL,$description='Product purchase')
    {
        $data['CompanyToken'] = $this->CompanyToken;
        $data['Request'] = 'createToken';
        $data['Transaction'] = [
            'PaymentAmount' =>$amount,
            'PaymentCurrency'=>$currency,
            'CompanyRef' => $reference,
            'RedirectURL'=>$redirectURL,
            'BackURL'=>$backURL,
            'CompanyRefUnique' => 0,
            'PTL' => 5,

        ];
        $data['Services'] = [
            'Service' => [
                [
                    'ServiceType' => $serviceID,
                    'ServiceDescription' => $description,
                    'ServiceDate'=>date_create('now')->format('Y/m/d H:i')
                ]
            ]
        ];


        $payload = ArrayToXml::convert($data, 'API3G',true, 'UTF-8',);
        $response = $this->request($payload);

        return $response;
    }

    public function verifyTransaction($transactionToken)
    {
        $data['CompanyToken'] = $this->CompanyToken;
        $data['Request'] = 'verifyToken';
        $data['TransactionToken'] = $transactionToken;
        $payload = ArrayToXml::convert($data, 'API3G',true, 'UTF-8',);
        $response = $this->request($payload);

        return $response;
    }

    public function emailToToken($transactionToken)
    {
        $data['CompanyToken'] = $this->CompanyToken;
        $data['Request'] = 'emailToToken';
        $data['TransactionToken'] = $transactionToken;

        $payload = ArrayToXml::convert($data, 'API3G',true, 'UTF-8',);
        $response = $this->request($payload);

        return $response;
    }

    private function request($payload)
    {
        $client = new Client(['headers' => [ 'Content-Type' => 'application/xml' ],
            'verify'=> base_path('/cacert.pem'),'http_errors'=>true]);

        $response = null;

        try{
            $response = $client->request('POST', $this->apiUrl, [
                'body' => $payload
            ]);

        }catch(\Exception $e)
        {
            report($e);
        }

        if(is_null($response))
            return  null;

        $responseBody = @$response->getBody()->getContents();
        return (object)XmlToArray::convert($responseBody);
    }

}
