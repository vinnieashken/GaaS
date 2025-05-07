<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use App\Jobs\NotifyWebhook;
use App\Models\Order;
use DateTime;
use Illuminate\Http\Request;

class MpesaController extends Controller
{

    public function qrcode(Request $request)
    {

    }
    public function validation_callback(Request $request)
    {
        $body = $request->getContent();

        $data = json_decode($body, false);
        $reference = $data->BillRefNumber;
        $amount = $data->TransAmount;
        $shortCode = $data->BusinessShortCode;
        $msisdn = $data->MSISDN;

        $order = Order::where('reference', $reference)->first();
        if ($order) {
            if($order->amount <= $amount){
                return response()->json([
                    "ResultCode" => "0",
                    "ResultDesc" => "Success",
                ]);
            }
        }

        return response()->json([
            "ResultCode" => "GT001",
            "ResultDesc" => "Rejected",
        ]);
    }
    public function stk_callback(Request $request)
    {
        $body = $request->getContent();

        $data = json_decode($body, false);
        $cache = json_decode($body, true);
        $data = $data->Body->stkCallback;
        $order =  Order::where('provider_code',$data->CheckoutRequestID)->first();
        if(!$order)
        {
            return response()->json([
                "ResultCode" => "0",
                "ResultDesc" => "Success",
            ]);
        }
        if($order->status == Order::STATUS_SUCCESS){
            return response()->json([
                "ResultCode" => "0",
                "ResultDesc" => "Success",
            ]);
        }
        $resultdesc = $data->ResultDesc;

        $amount = 0;
        $receipt = null;
        $phone = null;
        $status = Order::STATUS_FAILED;
        if($data->ResultCode == "0"){
            $status = Order::STATUS_SUCCESS;
            $order->paid = true;
            $items = $data->CallbackMetadata->Item;
            foreach($items as $item){
                switch($item->Name){
                    case "Amount":
                        $amount = $item->Value;
                        break;
                    case "MpesaReceiptNumber":
                        $receipt = $item->Value;
                        break;
                    case "PhoneNumber":
                        $phone = $item->Value;
                        break;
                }
            }
        }

        $order->receipt = $receipt;
        $order->provider_final_response = $resultdesc;
        $order->provider_final_response_data = $cache;
        $order->status = $status;
        $order->save();

        if($order->callback_url != null)
        {
            NotifyWebhook::dispatch($order->callback_url,webhook_payload($order),"gateway",$order->id);
        }

        $order->transaction()->update([
            "amount_paid" => $amount,
            "receipt" => $receipt,
            "status" => $status,
            "result" => $resultdesc,
            "provider_response" => $cache
        ]);

        return response()->json([
            "ResultCode" => "0",
            "ResultDesc" => "Success",
        ]);
    }

    public function c2b_callback(Request $request)
    {
        $body = $request->getContent();
        $cache = json_decode($body, true);
        $data = json_decode($body, false);
        $receipt = $data->TransID;
        $amount = $data->TransAmount;
        $reference = $data->BillRefNumber;
        $shortCode = $data->BusinessShortCode;
        $msisdn = $data->MSISDN;
        $firstname = $data->FirstName;
        $middlename = $data->MiddleName;
        $lastname = $data->LastName;

        $order = Order::where('identifier', $reference)->first();

        if($order)
        {
            if($order->status == Order::STATUS_SUCCESS){
                return response()->json([
                    "ResultCode" => "0",
                    "ResultDesc" => "Success",
                ]);
            }

            $status = Order::STATUS_PARTIALLY_PAID;
            if($order->amount <= $amount){
                $status = Order::STATUS_SUCCESS;
            }
            $order->transaction()->update([
                "amount_paid" => $amount,
                "receipt" => $receipt,
                "status" => $status,
                "result" => "c2b callback processed successfully",
                "provider_response" => $cache
            ]);

            $order->receipt = $receipt;
            $order->status = $status;
            $order->paid = true;
            $order->provider_final_response = "c2b callback processed successfully";
            $order->provider_final_response_data = $cache;
            $order->save();

            if($order->callback_url != null)
            {
                NotifyWebhook::dispatch($order->callback_url,webhook_payload($order),"gateway",$order->id);
            }
        }

        return response()->json([
            "ResultCode" => "0",
            "ResultDesc" => "Success",
        ]);
    }

    public function query_status_callback(Request $request)
    {
        $body = $request->getContent();

        $data = json_decode($body, false);
        $cache = json_decode($body, true);
        $result_code = $data->Result->ResultCode;
        $description     = @$data->Result->ResultDesc;
        $reference  = @$data->Result->ReferenceData->ReferenceItem->Value;

        $order = Order::where('identifier', $reference)->first();

        if($order)
        {
            $status = $order->status;

            if($result_code == 0)
            {
                $result_params = @$data->Result->ResultParameters->ResultParameter ?? [];
                $customer_details = '';
                $amount      = '';
                $receipt          = '';
                $transTime        = '';

                foreach ($result_params as $param)
                {
                    switch ($param->Key)
                    {
                        case "DebitPartyName":
                            $customer_details = $param->Value;
                            break;
                        case "TransactionStatus":
                            $status = $param->Value;
                            break;
                        case "Amount":
                            $amount = $param->Value;
                            break;
                        case "ReceiptNo":
                            $receipt = $param->Value;
                            break;
                        case "FinalisedTime":
                            $transTime = $param->Value;
                            break;
                    }
                }

                $name = @explode("-", $customer_details)[1];
                $name = trim($name);
                $MSISDN    = @explode("-", $customer_details)[0];
                $MSISDN    = trim($MSISDN);
                $timestamp = DateTime::createFromFormat('YmdHis', $transTime);
                $order->receipt = $receipt;
                if($timestamp > $order->created_at)
                {
                    if($amount >= $order->amount){
                        $status = Order::STATUS_SUCCESS;
                    }
                }
                else{
                    $status = Order::STATUS_FAILED;
                    $description = "Timestamp mismatch";
                }
                $order->transaction()->update([
                    'receipt' => $receipt,
                    'amount_paid' => $amount,
                    'status' => $status,
                    'result' => $description,
                    'provider_response' => $cache,
                ]);
            }
            $order->provider_final_response = $description;
            $order->provider_final_response_data = $cache;
            $order->status = $status;
            $order->save();
        }

        return response()->json([
            "ResultCode" => "0",
            "ResultDesc" => "Success",
        ]);
    }
    public function queue_timeout(Request $request)
    {
        $body = $request->getContent();
        logger($body);
    }
}
