<?php

namespace App\Http\Controllers\Api\V1;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0',
    description: 'This API allows clients to interact with the payment gateway. It requires an `appkey` header and a `Bearer` token for <b>authentication</b>.',
    title: 'Payments Gateway API',
)]
#[OA\SecurityScheme(
    securityScheme: 'BearerAuth',
    type: 'http',
    bearerFormat: 'JWT',
    scheme: 'bearer',
)]
#[OA\SecurityScheme(
    securityScheme: 'AppKey',
    type: 'apiKey',
    name: 'appkey',
    in: 'header',
)]
#[OA\Tag(name: 'Authentication', description: 'Authentication endpoints')]
#[OA\Tag(name: 'Gateways', description: 'Available payment gateways')]
#[OA\Tag(name: 'Payment', description: 'Payments endpoints')]
class SwaggerDefinitions
{

}
