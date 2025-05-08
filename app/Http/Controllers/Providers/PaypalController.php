<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use App\Jobs\NotifyWebhook;
use App\Models\Order;
use App\Utils\PaypalUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaypalController extends Controller
{
    //
    public function callback(Request $request)
    {
        $body = $request->getContent();

        $data = json_decode($body, false);
        $cache = json_decode($body, true);

        $event_type = $data->event_type;

        if($event_type == 'CHECKOUT.ORDER.APPROVED'){

            $reference = @$data->resource->purchase_units[0]->invoice_id;
            $order = Order::with(['gateway'])->where('identifier', $reference)->first();
            if($order){
                $config = $order->gateway->config;
                $util = new PaypalUtil($config->api_url,$config->client_id,$config->client_secret);
                $response = $util->captureOrder($order->provider_code);
                if(@$response->status == 'COMPLETED'){
                    return response()->json(['status' => 'success','message' => 'Capture sucess']);
                }
            }
        }

        if(!str_starts_with($event_type, 'PAYMENT.')){
            return response()->json(['status'=>'success']);
        }

        $reference = $data->resource->invoice_id;
        $resultdesc = $data->summary;
        $status = Order::STATUS_FAILED;
        $amount = 0;
        $receipt = null;
        $paid = false;

        if($event_type == 'PAYMENT.CAPTURE.COMPLETED'){
            $status = Order::STATUS_SUCCESS;
            $amount = $data->resource->amount->value;
            $receipt = $data->resource->id;
            $paid = true;
        }

        $order = Order::where('identifier', $reference)->first();

        if(!$order){
            Log::info('Order not found: '.$reference);
            return response()->json(['status'=>'success']);
        }
        $order->receipt = $receipt;
        $order->provider_final_response = $resultdesc;
        $order->provider_final_response_data = $cache;
        $order->status = $status;
        $order->paid = $paid;
        $order->save();

        $order->transaction()->update([
            "amount_paid" => $amount,
            "receipt" => $receipt,
            "status" => $status,
            "result" => $resultdesc,
            "provider_response" => $cache
        ]);

        if($order->callback_url != null)
        {
            NotifyWebhook::dispatch($order->callback_url,webhook_payload($order),"gateway",$order->id);
        }

        return response()->json(['status'=>'success']);
    }

    public function fallback(Request $request)
    {

    }
}
