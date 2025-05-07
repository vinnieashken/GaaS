<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class NotifyWebhook implements ShouldQueue,ShouldBeUnique
{
    use Queueable;

    protected $url;
    protected $payload;
    protected $secret;
    protected $orderId;

    public function uniqueId()
    {
        return $this->orderId;
    }

    public function __construct($url, $payload, $secret, $orderId)
    {
        $this->url = $url;
        $this->payload = $payload;
        $this->secret = $secret;
        $this->orderId = $orderId;
    }

    public function handle()
    {
        $order = Order::find($this->orderId);
        try{
            $response = Http::withHeaders([
                'Signature' => hash_hmac('sha256', json_encode($this->payload), $this->secret),
            ])->post($this->url, $this->payload);

            $result = [
                'status' => $response->status(),
                'body' => $response->body()
            ];
        }catch (\Exception $e){
            report($e);
            $result = [
                'status' => $e->getCode(),
                'body' => $e->getMessage()
            ];
        }
        $order->callback_response = $result;
        $order->save();
    }
}
