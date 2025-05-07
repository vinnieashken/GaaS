<?php

namespace App\Jobs;

use App\Models\Order;
use App\Utils\MpesaUtil;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class OrderSTKPush implements ShouldQueue
{
    use Queueable;

    public $order;
    public $gateway;
    /**
     * Create a new job instance.
     */
    public function __construct($order,$gateway)
    {
        $this->order = $order;
        $this->gateway = $gateway;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $config = (object)$this->gateway->config;
        $baseUrl = $config->base_url;
        $consumerKey = $config->consumer_key;
        $consumerSecret = $config->consumer_secret;
        $passKey = $config->passkey;
        $shortCode = $config->shortcode;
        $util = new MpesaUtil($baseUrl,$consumerKey,$consumerSecret,$passKey);
        $reference = $this->order->identifier;
        $amount = (int)$this->order->amount;
        $msisdn = $this->order->customer_phone;
        $callbackUrl = redirect_url(route('mpesa.stkcallback'));
        $result = $util->stKPush($shortCode,$reference,$amount,$msisdn,$callbackUrl);
        if($result)
        {
            if($result->ResponseCode==0)
            {
                $this->order->provider_code = $result->CheckoutRequestID;
                $this->order->status = Order::STATUS_PROCESSING;
            }
            $this->order->provider_initial_response = $result->ResponseDescription;
        }
        $result = (array)$result;
        $this->order->provider_initial_response_data = $result;
        $this->order->save();
    }
}
