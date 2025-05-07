<?php

namespace App\Http\Controllers;

use App\Models\Gateway;
use App\Traits\Response;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class GatewaysController extends Controller
{
    use Response;

    public function getGateways(Request $request){

        $size = $request->size ?? 1;
        $result = Gateway::where('status', 'active')
            ->select('id', 'name','identifier','provider','type','image_url')
            ->paginate($size);

       $meta = [
           'current_page' => $result->currentPage(),
           'per_page'     => $result->perPage(),
           'total'        => $result->total(),
           'total_pages'  => $result->lastPage(),
           'from'         => $result->firstItem(),
           'to'           => $result->lastItem(),
       ];

        return $this->success($result->items(),"Gateways retrieved successfully", $meta);
    }


    #[OA\Get(
        path: '/api/gateways',
        description: 'Get a list of allowed gateways',
        summary: 'List client gateways',
        security: [
            ['BearerAuth' => []],
            ['AppKey' => []]
        ],
        tags: ['Gateways'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'status',
                            description: "Determines if the call was successful or not",
                            type: 'boolean',
                            example: true
                        ),
                        new OA\Property(
                            property: 'message',
                            description: "Human friendly description of the process result",
                            type: 'string',
                            example: 'Payment request processed successfully'
                        ),
                        new OA\Property(
                            property: "data",
                            description: "An object containing the order request information",
                            type: "array",
                            items: new OA\Items( // <- You must define what the array contains
                                properties: [
                                    new OA\Property(property: "id", description: "The id of the payment gateway. used to initiate payments", type: "integer",example: "1"),
                                    new OA\Property(property: "name", description: "The name or brand of the payment gateway", type: "integer",example: "MPESA"),
                                    new OA\Property(property: "identifier", description: "A unique identifier of the gateway instance", type: "string",example: "MPESA200300"),
                                    new OA\Property(property: "provider", description: "The name of the payments system provider", type: "string",example: "safaricom"),
                                    new OA\Property(property: "type", description: "The type of the payment gateway.", type: "string",example: "mobile_money"),
                                    new OA\Property(property: "image_url", description: "A link to an image for branding purposes. can be null", type: "string",example: "https://example.com/image.png"),
                                    new OA\Property(property: "currencies", description: "The currencies supported by the gateway",
                                        type: "array",
                                        items: new OA\Items(
                                            properties: [
                                                new OA\Property(property: "code", description: "The currency code", type: "string",example: "USD"),
                                                new OA\Property(property: "name", description: "The full name", type: "string",example: "US dollars"),
                                            ]
                                        )
                                    ),
                                ],
                                type: "object",
                            )
                        ),
                        new OA\Property(
                            property: "meta",
                            description: "Contains pagination metadata. Useful in cases where data property is a list of objects",
                            properties: [
                                new OA\Property(property: "current_page", type: "integer", example: 1),
                                new OA\Property(property: "per_page", type: "integer", example: 10),
                                new OA\Property(property: "total", type: "integer", example: 100),
                                new OA\Property(property: "total_pages", type: "integer", example: 10),
                                new OA\Property(property: "from", type: "integer", example: 1),
                                new OA\Property(property: "to", type: "integer", example: 10)
                            ],
                            type: "object",
                        )
                    ],
                    type: "object",
                )
            ),
            new OA\Response(
                response: 400,
                description: "Failure",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',description: "Call success status", type: 'boolean',  example: false),
                        new OA\Property(property: 'message',description: "Error message", type: 'string',  example: 'Unable to fetch gateways'),
                        new OA\Property(
                            property: "errors",
                            description: "Detailed error messages",
                            type: "array",
                            items: new OA\Items(type: "string", example: "Failed to fetch items."),
                        )
                    ],
                    type: "object",
                )
            )
        ]
    )]
    public function getProfileGateways(Request $request)
    {
        $profile = $request->profile;

        $size = $request->size ?? 10;
        $result = Gateway::with(['currencies'=>function ($query) {
            return $query->select('code','name');
        }])->where('status', 'active')
            ->whereHas('profiles', function ($query) use ($profile) {
                $query->where('profile_id', $profile->id);
            })
            ->select('id', 'name','identifier','provider','type','image_url')
            ->paginate($size);

        $items = $result->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'identifier' => $item->identifier,
                'provider' => $item->provider,
                'type' => $item->type,
                'image_url' => $item->image_url,
                'currencies' => $item->currencies->map(function ($currency) {
                    return ['code' => $currency->code, 'name' => $currency->name];
                })
            ];
        });

        $result->setCollection($items);

        $meta = [
            'current_page' => $result->currentPage(),
            'per_page'     => $result->perPage(),
            'total'        => $result->total(),
            'total_pages'  => $result->lastPage(),
            'from'         => $result->firstItem(),
            'to'           => $result->lastItem(),
        ];

        return $this->success($result->items(),"Gateways retrieved successfully", $meta);
    }
}
