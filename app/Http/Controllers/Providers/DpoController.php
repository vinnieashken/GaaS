<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use App\Jobs\NotifyWebhook;
use App\Models\Transaction;
use App\Utils\DpoUtil;
use App\Models\Order;
use Illuminate\Http\Request;
use Spatie\WebhookServer\WebhookCall;

class DpoController extends Controller
{
    public function checkout(Request $request,Order $order)
    {
        $order->load(['gateway']);

        $util = new DpoUtil($order->gateway);
        $serviceType = @$order->gateway->config->service_type;
        $paymentUrl = @$order->gateway->config->redirect_url;
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
            return redirect($payUrl);
        }
        else{
            $order->provider_initial_response = $response->ResultExplanation;
            $order->provider_initial_response_data = json_encode($response);
            $order->save();
        }

        $query = http_build_query([
            'status' => Order::STATUS_ERROR,
            'message' => 'Unable to process transaction',
        ]);

        $charcter = '?';
        $charcter = str_contains($order->redirect_url, $charcter) ? '&' : '?';

        return redirect()->away($order->redirect_url.$charcter.$query);
    }

    public function callback(Request $request)
    {
        $token = $request->TransactionToken;
        $reference = $request->CompanyRef;

        $order = Order::with(['gateway'])->where('identifier',$reference)->limit(1)->first();

        $charcter = '?';
        $charcter = str_contains($order->redirect_url, $charcter) ? '&' : '?';

        $util = new DpoUtil($order->gateway);
        $response = $util->verifyTransaction($token);
        if($response)
        {
            $description = @$response->ResultExplanation;
            $order->provider_final_response = $response->ResultExplanation;
            $order->provider_final_response_data = json_encode($response);
            $amount = 0;
            $paid = 0;
            $receipt = null;
            if($response->Result == "000"){
                $status = Order::STATUS_SUCCESS;
                $amount = $response->TransactionAmount;
                $paid = 1;
                $receipt = $response->TransactionApproval;
            }
            else{
                $status = Order::STATUS_FAILED;
            }
            $order->receipt = $receipt;
            $order->paid = $paid;
            $order->status = $status;
            $order->save();

            //dd($response,$order);

            $transaction_data = [
                'provider_response' => json_encode($response),
                'result' => $description,
                'amount_paid' => $amount,
                'status' => $status,
                'receipt' => $receipt,
            ];

            $order->transaction()->update($transaction_data);

            if($order->callback_url != null)
            {
                NotifyWebhook::dispatch($order->callback_url,webhook_payload($order),"gateway",$order->id);
            }

            $query = http_build_query([
                'status' => $order->status,
                'reference' => $reference,
                'invoice_number' => $order->invoice_number,
                'paid' => (bool)$order->paid,
                'receipt' => $receipt,
                'message' => $description,
            ]);

            return redirect($order->redirect_url.$charcter.$query);
        }

        $query = http_build_query([
            'status' => Order::STATUS_ERROR,
            'message' => 'Unable to process transaction',
        ]);

        return redirect()->away($order->redirect_url.$charcter.$query);
    }
}
