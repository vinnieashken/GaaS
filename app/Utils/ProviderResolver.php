<?php

namespace App\Utils;

use App\Models\Gateway;
use App\Models\Order;

class ProviderResolver
{
    public $gateway;
    public $reference;
    public $amount;
    public $currency;
    public $order;
    public function __construct(Gateway $gateway,$order)
    {
        $this->gateway = $gateway;
        $this->order = $order;
    }

    public function resolve()
    {
        $result = [
            'redirect_url' => null,
            'display_info' => null,
        ];

        if($this->gateway->provider == 'safaricom'){
            $this->mpesa($this->gateway,$this->order);
            $result['display_info'] = [
                'paybill' => @$this->gateway->config->shortcode,
                'account_number' => $this->order->identifier,
                'amount' => $this->order->amount,
                'currency' => $this->order->currency,
            ];
        }
        elseif ($this->gateway->provider == 'dpo')
        {
            $res = $this->dpo($this->gateway,$this->order);
            $result['redirect_url'] = $res->pay_url; //add local dpo redirect url
        }
        elseif ($this->gateway->provider == 'paypal')
        {
            $this->paypal($this->gateway,$this->order);
        }
        return $result;
    }

    public function mpesa($gateway,$order)
    {
        $config = $gateway->config;
        $baseUrl = $config->base_url;
        $consumerKey = $config->consumer_key;
        $consumerSecret = $config->consumer_secret;
        $passKey = $config->passkey;
        $shortCode = $config->shortcode;
        $util = new MpesaUtil($baseUrl,$consumerKey,$consumerSecret,$passKey);
        $reference = $order->identifier;
        $amount = (int)$order->amount;
        $msisdn = $order->customer_phone;
        $callbackUrl = redirect_url(route('mpesa.stkcallback'));
        $result = null;
        if(!is_null($msisdn))
        {
            $result = $util->stKPush($shortCode,$reference,$amount,$msisdn,$callbackUrl);
            if($result)
            {
                if($result->ResponseCode==0)
                {
                    $order->provider_code = $result->CheckoutRequestID;
                    $order->status = Order::STATUS_PROCESSING;
                }
                $this->order->provider_initial_response = $result->ResponseDescription;
            }
            $result = (array)$result;
            $this->order->provider_initial_response_data = $result;
            $this->order->save();
        }
        return $result;
    }

    public function dpo($gateway,$order)
    {
        $util = new DpoUtil($gateway);
        $serviceType = @$gateway->config->service_type;
        $paymentUrl = @$gateway->config->redirect_url;
        $amount = $order->amount;
        $currency = $order->currency;
        $reference = $order->identifier;

        $callbackUrl = redirect_url(route('dpo.callback'));

        $response = $util->createToken($serviceType,$amount,$currency,$reference,$callbackUrl,$callbackUrl);

        if(is_null($response))
        {
            $response = $util->createToken($serviceType,$amount,$currency,$reference,$callbackUrl,$callbackUrl);
        }

        $order->attempted = true;

        if(@$response->Result === "000")
        {
            $token = $response->TransToken;
            $transref = $response->TransRef;
            $order->provider_code = $transref;
            $order->provider_initial_response = $response->ResultExplanation;
            $order->provider_initial_response_data = json_encode($response);
            $order->status = Order::STATUS_PROCESSING;
            $order->save();
            $payUrl = $paymentUrl.$token;
            $response->pay_url = $payUrl;
        }
        else{
            $order->provider_initial_response = $response->ResultExplanation;
            $order->provider_initial_response_data = json_encode($response);
            $order->save();
        }

        return $response;
    }

    public function paypal($gateway,$order)
    {
        $url = $gateway->config->api_url;
        $client_id = $gateway->config->client_id;
        $client_secret = $gateway->config->client_secret;
        $util = new PaypalUtil($url, $client_id, $client_secret);
        $result = $util->createOrder($order->amount,$order->identifier,$order->currency);
        if(!is_null($result) && @$result->status == "CREATED"){
            $order->status = Order::STATUS_PROCESSING;
            $order->provider_code = $result->id;
            $order->provider_initial_response = $result->status;
            $order->provider_initial_response_data = (array)$result;
            $order->save();
        }
        else{
            throw new \Exception($result->details[0]->description);
        }

    }
}
