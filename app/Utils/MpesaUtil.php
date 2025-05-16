<?php

namespace App\Utils;

use GuzzleHttp\Client;
use Mtownsend\XmlToArray\XmlToArray;

class MpesaUtil
{
    public $consumer_key;
    public $consumer_secret;
    public $passkey;

    public $baseurl;

    public function __construct($baseurl,$consumer_key, $consumer_secret, $passkey=null)
    {
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
        $this->passkey = $passkey;
        $this->baseurl = $baseurl;
    }

    public function encrypt_certificate($certfilepath, $passphrase)
    {
        $cert      = app_path($certfilepath)  ;
        $fp        = fopen($cert, "r");
        $publicKey = fread($fp, filesize($cert));
        fclose($fp);
        openssl_get_publickey($publicKey);
        openssl_public_encrypt($passphrase, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
        return base64_encode($encrypted);
    }

    public function token()
    {
        $credentials = base64_encode($this->consumer_key . ':' . $this->consumer_secret);
        return $this->request("GET",$this->baseurl.config('mpesa.auth_token_url'),json_encode([]),['Authorization' => 'Basic ' . $credentials]);
    }

    public function stKPush($shortcode,$reference,$amount,$msisdn,$callback_url,$description="payment for goods and services",$type="CustomerPayBillOnline")
    {
        $timestamp = date('YmdHis');
        $password  = base64_encode(string: $shortcode . $this->passkey . $timestamp);
        $data      = [
            'BusinessShortCode' => $shortcode,
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'TransactionType'   => $type,
            'Amount'            => $amount,
            'PartyA'            => $msisdn,
            'PartyB'            => $shortcode,
            'PhoneNumber'       => $msisdn,
            'CallBackURL'       => $callback_url,
            'AccountReference'  => $reference,
            'TransactionDesc'   => $description
        ];

        $token = $this->token()->access_token;

        return $this->request("POST",$this->baseurl.config('mpesa.stk_checkout_url'),json_encode($data),['Authorization' => 'Bearer ' . $token]);
    }

    public function stkCheckoutQuery($shortcode,$checkout_request_id)
    {
        $timestamp = date('YmdHis');
        $password  = base64_encode(string: $shortcode . $this->passkey . $timestamp);
        $data     = [
            'BusinessShortCode' => $shortcode,
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'CheckoutRequestID' => $checkout_request_id
        ];

        $token = $this->token()->access_token;
        return $this->request("POST",$this->baseurl.config('mpesa.stk_query_url'),json_encode($data),['Authorization' => 'Bearer ' . $token]);
    }

    public function registerC2BCallbackURL($shortcode,$confirmation_url,$validation_url,$response_type='Cancelled')
    {
        //Completed
        $data = [
            'ValidationURL'   => $validation_url,
            'ConfirmationURL' => $confirmation_url,
            'ResponseType'    => $response_type,
            'ShortCode'       => $shortcode
        ];

        $token = $this->token()->access_token;
        return $this->request("POST",$this->baseurl.config('mpesa.c2b_register_url'),json_encode($data),['Authorization' => 'Bearer ' . $token]);
    }

    public function QueryStatus($shortcode,$initiator,$initiator_password,$certificate_path,$receipt_number,$identifier_type,$callback_url,$timeout_url,$reference)
    {
        $data = [
            'Initiator'              => $initiator,
            'SecurityCredential'     => $this->encrypt_certificate($certificate_path,$initiator_password),
            'CommandID'              => 'TransactionStatusQuery',
            'TransactionID'          => $receipt_number,
            'PartyA'                 => $shortcode,
            'IdentifierType'         => $identifier_type,
            'ResultURL'              => $callback_url,
            'QueueTimeOutURL'        => $timeout_url,
            'Remarks'                => 'Paybill Payment check',
            'Occasion'               => $reference,
            //'OriginalConversationID' => '4fe9-4cd8-ab70-95b3e86ac48937724569'
        ];

        $token = $this->token()->access_token;
        return $this->request("POST",$this->baseurl.config('mpesa.transtat_url'),json_encode($data),['Authorization' => 'Bearer ' . $token]);
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
        }
        if(is_null($response))
            return  null;

        $responseBody = @$response->getBody()->getContents();
        return (object)json_decode($responseBody);
    }
}
