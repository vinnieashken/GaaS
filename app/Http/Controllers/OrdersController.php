<?php

namespace App\Http\Controllers;

use App\Jobs\OrderSTKPush;
use App\Models\Gateway;
use App\Models\Order;
use App\Models\Transaction;
use App\Traits\Response;
use App\Utils\PaypalUtil;
use App\Utils\ProviderResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class OrdersController extends Controller
{
    use Response;

    #[OA\Post(
        path: '/api/payment/initiate',
        description: 'Create a payment request',
        summary: 'Initiate Payment',
        security:[
            ['BearerAuth' => []],
            ['AppKey' => []]
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    required: ["gateway_id", "invoice_number", "amount", "currency", "customer_identifier", "msisdn", "callback_url"],
                    properties: [
                        new OA\Property(property: "gateway_id",description: "ID of the assigned gateway", type: "integer", example: 1,),
                        new OA\Property(property: "invoice_number",description: "Used to identify orders on your application", type: "string", example: "INV009"),
                        new OA\Property(property: "amount", description: "The amount to be billed",type: "number", format: "integer", example: 5),
                        new OA\Property(property: "currency",description: "The currency to be used. Must be supported by the gateway", type: "string", example: "KES"),
                        new OA\Property(property: "customer_identifier",description: "A way to identify your customer e.g email address", type: "string", example: "John@example.com"),
                        new OA\Property(property: "msisdn",description: "A phone number to be used for mobile payments.", type: "string", example: "2547XXXXXXX"),
                        new OA\Property(property: "callback_url",description: "A webhook URL to receive payment details. POST only.", type: "string", format: "uri", example: "https://example.com"),
                        new OA\Property(property: "redirect_url",description: "A url to redirect to: used mainly by card providers. GET only", type: "string", format: "uri", example: "https://example.com")
                    ],type: "object"
                )
            )
        ),
        tags: ['Payment'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',description: "Determines if the call was successful or not", type: 'boolean', example: 'true'),
                        new OA\Property(property: 'message',description: "Human friendly description of the process result", type: 'string', example: 'Payment request processed successfully'),
                        new OA\Property(
                            property: "data",
                            description: "An object containing the order request information",
                            properties: [
                                new OA\Property(
                                    property: "payment_request_identifier",
                                    description: "UUID of the payment request",
                                    type: "string",
                                    example: "d45c0ae4-065b-4af5-a81f-1e7fd29199cb"
                                ),
                                new OA\Property(
                                    property: "invoice_number",
                                    description: "Invoice number associated with the payment",
                                    type: "string",
                                    example: "INV009"
                                ),
                                new OA\Property(
                                    property: "reference",
                                    description: "Unique reference for the payment",
                                    type: "string",
                                    example: "AJTWCQIG"
                                ),
                                new OA\Property(
                                    property: "amount",
                                    description: "Payment amount",
                                    type: "number",
                                    format: "float",
                                    example: 1
                                ),
                                new OA\Property(
                                    property: "currency",
                                    description: "Currency of the payment",
                                    type: "string",
                                    example: "KES"
                                ),
                                new OA\Property(
                                    property: "customer_identifier",
                                    description: "Customer identifier (email or unique key)",
                                    type: "string",
                                    example: "john@example.com"
                                ),
                                new OA\Property(
                                    property: "provider_code",
                                    description: "A unique order code from the payment provider. useful for paypal integration",
                                    type: "string",
                                    example: "13ebcg3747790"
                                ),
                                new OA\Property(
                                    property: "redirect_url",
                                    description: "Optional redirect URL for further payment processing",
                                    type: "string",
                                    format: "uri",
                                    nullable: true,

                                ),
                                new OA\Property(
                                    property: "display_info",
                                    description: "Extra instructions for display to the customer to assist in making the payment. Useful for mobile payments.",
                                    properties: [
                                        new OA\Property(
                                            property: "paybill",
                                            description: "Paybill number for mobile payments",
                                            type: "string",
                                            example: "500400"
                                        ),
                                        new OA\Property(
                                            property: "account_number",
                                            description: "Account number (usually the reference)",
                                            type: "string",
                                            example: "AJTWCQIG"
                                        ),
                                        new OA\Property(
                                            property: "amount",
                                            description: "Amount to be paid",
                                            type: "number",
                                            format: "float",
                                            example: 1
                                        ),
                                        new OA\Property(
                                            property: "currency",
                                            description: "Currency code",
                                            type: "string",
                                            example: "KES"
                                        )
                                    ],
                                    type: "object"
                                )
                            ],
                            type: "object",
                        ),

                        new OA\Property(
                            property: "meta",
                            description: "Contains pagination metadata. Useful in cases where data property is a list of objects",
                            properties: [
                                new OA\Property(
                                    property: "current_page",
                                    type: "integer",
                                    example: 1
                                ),
                                new OA\Property(
                                    property: "per_page",
                                    type: "integer",
                                    example: 1
                                ),
                                new OA\Property(
                                    property: "total",
                                    type: "integer",
                                    example: 1
                                ),
                                new OA\Property(
                                    property: "total_pages",
                                    type: "integer",
                                    example: 1
                                ),
                                new OA\Property(
                                    property: "from",
                                    type: "integer",
                                    example: 1
                                ),
                                new OA\Property(
                                    property: "to",
                                    type: "integer",
                                    example: 1
                                )
                            ],
                            type: "object"
                        )
                    ]
                )
            ),

            //400
            new OA\Response(
                response: 400,
                description: "Failure",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',description: "Determines if the call was successful or not", type: 'boolean', example: 'false'),
                        new OA\Property(property: 'message',description: "Human friendly description of the process result", type: 'string', example: 'Validation failed'),
                        new OA\Property(
                            property: "errors",
                            description: "Array of more detailed error messages",
                            type: "array",
                            items: new OA\Items(
                                type: "string",
                                example: "The msisdn field must be 12 digits."
                            )
                        )
                    ]
                )
            )
        ],
        callbacks: [
            'Payment Journey Completed' => [
                '{$request.body.callback_url}' => [
                    'post' => [
                        'requestBody' => new OA\RequestBody(
                            description: 'Webhook payload sent back to you when payment journey is complete',
                            required: true,
                            content: [
                                new OA\MediaType(
                                    mediaType: 'application/json',
                                    schema: new OA\Schema(
                                        required: ['status', 'message'],
                                        properties: [
                                            new OA\Property(property: 'status', description:"The status of the transaction. SUCCESS or FAILED",type: 'string', example: 'SUCCESS'),
                                            new OA\Property(property: 'message',description:"Human friendly description of the transaction", type: 'string', example: 'The service request is processed successfully.'),
                                            new OA\Property(
                                                property: 'payment_request',
                                                description: "Contains the initial payment request details",
                                                properties: [
                                                    new OA\Property(
                                                        property: "payment_request_identifier",
                                                        description: "UUID of the payment request",
                                                        type: "string",
                                                        example: "d45c0ae4-065b-4af5-a81f-1e7fd29199cb"
                                                    ),
                                                    new OA\Property(
                                                        property: "invoice_number",
                                                        description: "Invoice number associated with the payment",
                                                        type: "string",
                                                        example: "INV009"
                                                    ),
                                                    new OA\Property(
                                                        property: "reference",
                                                        description: "Unique reference for the payment request",
                                                        type: "string",
                                                        example: "X1ABCEFE"
                                                    ),
                                                    new OA\Property(
                                                        property: "amount",
                                                        description: "Payment amount",
                                                        type: "number",
                                                        format: "float",
                                                        example: 1
                                                    ),
                                                    new OA\Property(
                                                        property: "currency",
                                                        description: "Currency of the payment",
                                                        type: "string",
                                                        example: "KES"
                                                    ),
                                                ],
                                                type: "object"
                                            ),
                                            new OA\Property(property: 'paid',description:"Determines if the payment was done or not. true or false", type: 'boolean', example: 'true'),
                                            new OA\Property(property: 'receipt',description:"Receipt number from the gateway provide if the payment was successfull, null otherwise", type: 'string', example: 'AJTWCQIG'),
                                            new OA\Property(property: 'transaction_date',description:"Date and time the transaction was completed", type: 'string', format: 'date-time', example: '2025-05-06 10:15:00'),

                                        ]
                                    )
                                )
                            ]
                        ),
                        'responses' => [
                            '200' => [
                                'description' => 'Your server should return this HTTP status to acknowledge receipt.',
                            ]
                        ]
                    ]
                ]
            ]
        ]//end of callbacks array
    )]
    public function PaymentRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gateway_id' => 'required',
            'invoice_number' => 'required',
            'amount' => 'required',
            'currency' => 'required',
            'customer_identifier' => 'required',
            'msisdn' => 'sometimes|numeric|digits:12',
        ]);

        if ($validator->fails()) {
            return $this->error("Validation failed!",[$validator->errors()->first()],400);
        }

        $user = $request->user;
        $profile = $request->profile;

        if(!$profile)
        {
            return $this->error("Client profile not found!",['invalid client details'],400);
        }

        $gateway = Gateway::with(['currencies'])->where('id',$request->gateway_id)->first();

        if(!$gateway){
            return $this->error("Gateway not found!",['Invalid gateway id passed'],400);
        }

        if($gateway->status != 'active'){
            return $this->error("Gateway is inactive!",['Gateway was disabled'],400);
        }

        if(!in_array($request->currency,$gateway->currencies->pluck('code')->toArray())){
            return $this->error("Currency not supported by gateway",['Invalid currency'],400);
        }

        if($gateway->type == 'mobile_money')
        {
            $validator = Validator::make($request->all(), [
                'callback_url' => 'required',
                'msisdn' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->error("Validation failed!",[$validator->errors()->first()],400);
            }
        }
        elseif($gateway->provider == 'dpo'){
            $validator = Validator::make($request->all(), [
                'redirect_url' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->error("Validation failed!",[$validator->errors()->first()],400);
            }
        }

        $profile_gateway = $profile->gateways()->whereIn('gateway_id',[$request->gateway_id])->first();

        if(!$profile_gateway)
        {
            return $this->error("Gateway not linked to client profile!",['Invalid client profile gateway configuration'],400);
        }

        $msisdn = $request->msisdn;
        if(str_starts_with($msisdn, '0'))
            $msisdn = preg_replace('/0/', '254', $msisdn, 1);

        if(str_starts_with($msisdn, '+'))
            $msisdn = preg_replace('/\+/', '', $msisdn, 1);

        $result = DB::transaction(function () use ($gateway,$profile_gateway,$request,$user,$profile,$msisdn) {
            try{
                $order = Order::create([
                    'user_id' => $user->id,
                    'profile_id' => $profile->id,
                    'gateway_id' => $gateway->id,
                    'invoice_number' => $request->invoice_number,
                    'currency' => $request->currency,
                    'amount' => $request->amount,
                    'customer_identifier' => $request->customer_identifier,
                    'customer_phone' => $msisdn,
                    'callback_url' => trim($request->callback_url),
                    'redirect_url' => trim($request->redirect_url),
                    'status' => Order::STATUS_PENDING,
                ]);

                $transaction = Transaction::create([
                    'order_id' => $order->id,
                    'gateway_id' => $gateway->id,
                    'provider' => $gateway->provider,
                    'amount' => $order->amount,
                    'amount_paid' => 0,
                    'currency' => $order->currency,
                    'receipt' => null,
                    'status' => Order::STATUS_PENDING,
                    'result' => null,
                    'provider_response' => null,
                ]);

                $providerResolver = new ProviderResolver($gateway,$order);
                $result = $providerResolver->resolve();

                return [
                    'status' => true,
                    'order' => $order,
                    'transaction' => $transaction,
                    'provider_result' => $result,
                ];

            }catch (\Exception $e){
                report($e);
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => $e->getMessage(),
                ];
            }
        });

        $display_info = null;
        $redirect_url = null;
        $result = (object)$result;
        if($result->status){
            $order = $result->order;
            $display_info = $result->provider_result['display_info'];
            $redirect_url = $result->provider_result['redirect_url'];
        }
        else{
            return $this->error("Unable to initialize payment request",[$result->message],400);
        }

        $data = [
            'payment_request_identifier' => $order->uuid,
            'invoice_number' => $order->invoice_number,
            'reference' => $order->identifier,
            'amount' => $order->amount,
            'currency' => $order->currency,
            'customer_identifier' => $order->customer_identifier,
            'provider_code' => $order->provider_code,
            'redirect_url' => $redirect_url,
            'display_info' => $display_info
        ];

        return $this->success($data,'Payment request processed successfully',default_meta());
    }
}
