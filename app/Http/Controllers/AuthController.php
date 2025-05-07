<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\ProfileToken;
use App\Models\User;
use App\Traits\Response;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Client;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public $expiry;
    public function __construct(){
        $this->expiry = env('APP_TOKEN_EXPIRY');
    }
    use Response;
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $credentials = array_merge($credentials, ['status' => 'active']);

        if (Auth::attempt($credentials)) {
            // Authentication passed, regenerate session
            $request->session()->regenerate();

            return redirect()->intended('/');
        }

        return redirect()->back()->withErrors(['email' => 'Invalid email or password']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }


    public function showRegisterForm()
    {

    }

    public function register(Request $request)
    {

    }

    public function showLinkRequestForm(Request $request)
    {
        return view('auth.passwords.email');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? redirect()->back()->with('status', "Passsword reset email sent successfully.")
            : redirect()->back()->withErrors(['email' => 'Password reset email could not be sent.']);
    }

    public function showResetForm(Request $request, $token)
    {
        //$token = $request->route()->parameter('token');
        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    public function resetPassword(Request $request)
    {
        $request->validate(['password' => 'required|string|confirmed',
            'email' => 'required|string|email|exists:users,email']);

        $password = Hash::make($request->password);
        $user = User::where('email',$request->email)->first();
        $user->password = $password;
        $user->save();
        return redirect()->route('login.form')->with('success', 'Password reset successfully. Login with your new password.');
    }

    public function userToken(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->error("Validation failed!",[$validator->errors()->first()],400);
        }

        $user = User::where('email',$request->email)->first();
        $client = Client::limit(0)->orderBy('id','asc')->first();
        $internalRequest = Request::create('/oauth/token', 'POST', [
            'grant_type'    => 'password',
            'client_id'     => $client->id,
            'client_secret' => $client->secret,
            'username'      => $request->email,
            'password'      => $request->password,
            'scope'         => '',
        ]);

        $key = 'user_token_'.$request->email;
        $response = app()->handle($internalRequest);
        $response = json_decode($response->getContent(), true);

        if(array_key_exists('access_token', $response)){
            $data = [
                'access_token' => $response['access_token'],
                'refresh_token' => $response['refresh_token'],
                'token_type'   => 'Bearer',
                'expires_at'   => Carbon::now()->addSeconds($response['expires_in'])->format('Y-m-d H:i:s'),
                'user'         => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ];
            return $this->success($data,'Client token generated successfully',default_meta());
        }

        return $this->error("Client token could not be generated!",[@$response['message'].' '.@$response['hint'] ?? 'Invalid credentials supplied!'],404);
    }


    #[OA\Post(
        path: '/api/auth/client/token',
        description: 'Generate an OAUTH token for use with other endpoints',
        summary: 'Generate an access token',
        security:[
            ['AppKey' => []]
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    required: ["client_key", "client_secret"],
                    properties: [
                        new OA\Property(property: "client_key", description:"A unique key that identifies a client",type: "string",example:"HpPJd3I7Iwtl8kx1"),
                        new OA\Property(property: "client_secret", description:"A passcode attached to a client",type: "string",example:"gjzUkx1rWUUprwiHpPJd3I7Iwtl8rWUUprwiHpPJd"),
                    ],
                    type: "object"
                )
            )
        ),
        tags:["Authentication"],
        responses: [
            new OA\Response(
            response: 200,
            description: "Success",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status',description: "Determines if the call was successful or not", type: 'boolean', example: 'true'),
                    new OA\Property(property: 'message',description: "Human friendly description of the process result", type: 'string', example: 'Payment request processed successfully'),
                    new OA\Property(
                        property: "data",
                        description: "An object containing the authentication information",
                        properties: [
                            new OA\Property(
                                property: "token_type",
                                description: "Determines the type of the token",
                                type: "string",
                                example: "bearer",
                            ),
                            new OA\Property(
                                property: "access_token",
                                description: "A JWT access token for authentication",
                                type: "string",
                                example: "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIzIiwianRpIjoiZDBhYjcyNGU0NWNhNTliODMzMGIwMjY0NGIwM2VjM2U4ZWEzZDUyM2I3ZTJkZjBiYmY5ODNiMTUwNTdhODg3NmFlYWRkMjk2MzJhN2I0ODYiLCJpYXQiOjE3NDY0NTEzMjcuOTQwNDAxLCJuYmYiOjE3NDY0NTEzMjcuOTQwNDAzLCJleHAiOjE3NDkwNDMzMjcuOTA2OTYsInN1YiI6IjEiLCJzY29wZXMiOltdfQ.h4lJtxMfJeBYHgFpFSnsYowmzNyRO4JPB72by-gLIEe7H2mb1d-hcW9JxKQeFwilvzPWEbffhp4DRflpV7Wd2Jkoe6R7-klhtcz62IpMhy2glNEuXBG1VNADGEWBOxH9r2OL4XxFXFx66cEBS4aR6hS_jYjdKFBMMAX6p-CV158gl9N8DyT2-YfiUkLByna-VKyQpEzJsgNyQ2_yPGU5amBnjl95jd6yE_rqKxEkvfStkWkNCo5zWAhytGTnMUAqzEpiz8JQyt-Wb6NFbTrFQB5_pk4nzmNiNnQSAGcRqgiRNeebyINrnP1NtArsNOhS_NLZrPYsRe_ezj2b4fuWeyKwz2sorndsEFJtEX_U3tRc-czXl3tEzFM3yNKzsyM_vnT2F-BlQE6tQFREBnSIVyqzXcphrWpPpV9fWCLNSxMLLFGglhY9yNJP_7WmzvHxFMdGSQnuJ3UonKWCH_23XqKnW-_2aFMExHAjbG20qdNP0LN7a_8sIN4Dlhgv438jOt9mBTZYjwb42oFaU-o-k2etEAUdufj7YMAVL4wynVFTsVPB5_nEJhJz8fpASX3iE6-E8DGRlGC9tw2OsWAfzv6ZZHpKbs-PMoIldrrBeV8VEC5EGd41tq1318TeeNPjbkL0v3Q6oPAb4JZpkqhnmY8hXrXkkBzVHBKbBd1TobA"
                            ),
                            new OA\Property(
                                property: "refresh_token",
                                description: "A JWT token used to renew an expired access token",
                                type: "string",
                                example: "def50200bd1bcfc808693fd00589467d4437e39c297a435e699b2d23f9d2daffa65e35c08f52cac588c339355f783a2f76c22feb6589b7f385b0227ddedeedcfdb5398593fb40c44fba6fd1dcf40e2bcd1e5b328843eaaaa83f0f6fe8c0dfb5eeccbd684e64912e449abc94be8929fa736f8ded72d4a4bf29976bf6edb898990f426555f052ff2edbee9c41e4dbef7ec20a176eec6164d708e58db9f728e8f18772e4314a5c99cfb89f5178700b989c3acd95fd2f3d02d5f4f7e44498503c4dfaa7cb4412b3a434320d1ad4cb75a5523346f175bf85b0f445b1ccd58f9ecac3fd7f92980d5e7c5a168c5af78a728b77fe9c65dab049c143e7c66368e80f4c4eaade0a640632389446e610bb2bc9d0bd53e22f6fe5540b8feb42e553542d5fcb2cae79ee80a4521f1cefc665991913665d1e04d960aafdc5de9258f3ac1e6a50f473070f80b5975ec0326bafe9f8758d9ea9b3640333e93909e9f55013e50048168"
                            ),
                            new OA\Property(
                                property: "expires_at",
                                description: "Date and time of access token expiry",
                                type: "date",
                                example: "2025-06-04 13:22:08"
                            ),
                            new OA\Property(
                                property: "user",
                                description: "User account information",
                                properties: [
                                    new OA\Property(property: "id", description: "User ID", type: "string", example: "1"),
                                    new OA\Property(property: "name",description: "User name", type: "string", example: "John Doe"),
                                    new OA\Property(property: "email",description: "User email", type: "string", example: "johndoe@example.com"),
                                ],
                                type: "object",
                            ),
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
                        new OA\Property(property: 'message',description: "Human friendly description of the process result", type: 'string', example: 'Client not found!'),
                        new OA\Property(
                            property: "errors",
                            description: "Array of more detailed error messages",
                            type: "array",
                            items: new OA\Items(
                                type: "string",
                                example: "Invalid client details"
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function clientToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_key' => 'required|string',
            'client_secret' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error("Validation failed!",[$validator->errors()->first()],400);
        }

        $profile = Profile::where('key',$request->client_key)->where('secret',$request->client_secret)->first();

        if (!$profile) {
            return $this->error("Client not found!",['Invalid client details'],400);
        }

        $user = User::where('id', $profile->user_id)
            ->select('id','name','email')
            ->first();

        $response = generate_tokens($profile);

        if(array_key_exists('access_token', $response)){
            $data = [
                'token_type'   => 'Bearer',
                'access_token' => $response['access_token'],
                'refresh_token' => $response['refresh_token'],
                'expires_at'   => Carbon::parse($response['expires_at'])->format('Y-m-d H:i:s'),
                'user'         => $user
            ];

            return $this->success($data,'Client token generated successfully',default_meta());
        }

        return $this->error("Client token could not be generated!",['Invalid credentials supplied!'],404);
    }

    #[OA\Post(
        path: '/api/auth/refresh/token',
        description: 'Regenerate an access token',
        summary: 'Renew access token',
        security:[
            ['AppKey' => []]
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    required: ["refresh_token"],
                    properties: [
                        new OA\Property(property: "refresh_token", description:"JWT token returned from auth API",type: "string",example:"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.WyJhM0VieGVaT3lwQ.."),
                    ],
                    type: "object"
                )
            )
        ),
        tags:["Authentication"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status',description: "Determines if the call was successful or not", type: 'boolean', example: 'true'),
                        new OA\Property(property: 'message',description: "Human friendly description of the process result", type: 'string', example: 'Payment request processed successfully'),
                        new OA\Property(
                            property: "data",
                            description: "An object containing the authentication information",
                            properties: [
                                new OA\Property(
                                    property: "token_type",
                                    description: "Determines the type of the token",
                                    type: "string",
                                    example: "bearer",
                                ),
                                new OA\Property(
                                    property: "access_token",
                                    description: "A JWT access token used for authentication",
                                    type: "string",
                                    example: "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIzIiwianRpIjoiZDBhYjcyNGU0NWNhNTliODMzMGIwMjY0NGIwM2VjM2U4ZWEzZDUyM2I3ZTJkZjBiYmY5ODNiMTUwNTdhODg3NmFlYWRkMjk2MzJhN2I0ODYiLCJpYXQiOjE3NDY0NTEzMjcuOTQwNDAxLCJuYmYiOjE3NDY0NTEzMjcuOTQwNDAzLCJleHAiOjE3NDkwNDMzMjcuOTA2OTYsInN1YiI6IjEiLCJzY29wZXMiOltdfQ.h4lJtxMfJeBYHgFpFSnsYowmzNyRO4JPB72by-gLIEe7H2mb1d-hcW9JxKQeFwilvzPWEbffhp4DRflpV7Wd2Jkoe6R7-klhtcz62IpMhy2glNEuXBG1VNADGEWBOxH9r2OL4XxFXFx66cEBS4aR6hS_jYjdKFBMMAX6p-CV158gl9N8DyT2-YfiUkLByna-VKyQpEzJsgNyQ2_yPGU5amBnjl95jd6yE_rqKxEkvfStkWkNCo5zWAhytGTnMUAqzEpiz8JQyt-Wb6NFbTrFQB5_pk4nzmNiNnQSAGcRqgiRNeebyINrnP1NtArsNOhS_NLZrPYsRe_ezj2b4fuWeyKwz2sorndsEFJtEX_U3tRc-czXl3tEzFM3yNKzsyM_vnT2F-BlQE6tQFREBnSIVyqzXcphrWpPpV9fWCLNSxMLLFGglhY9yNJP_7WmzvHxFMdGSQnuJ3UonKWCH_23XqKnW-_2aFMExHAjbG20qdNP0LN7a_8sIN4Dlhgv438jOt9mBTZYjwb42oFaU-o-k2etEAUdufj7YMAVL4wynVFTsVPB5_nEJhJz8fpASX3iE6-E8DGRlGC9tw2OsWAfzv6ZZHpKbs-PMoIldrrBeV8VEC5EGd41tq1318TeeNPjbkL0v3Q6oPAb4JZpkqhnmY8hXrXkkBzVHBKbBd1TobA"
                                ),
                                new OA\Property(
                                    property: "refresh_token",
                                    description: "A JWT token used to renew an expired access token",
                                    type: "string",
                                    example: "def50200bd1bcfc808693fd00589467d4437e39c297a435e699b2d23f9d2daffa65e35c08f52cac588c339355f783a2f76c22feb6589b7f385b0227ddedeedcfdb5398593fb40c44fba6fd1dcf40e2bcd1e5b328843eaaaa83f0f6fe8c0dfb5eeccbd684e64912e449abc94be8929fa736f8ded72d4a4bf29976bf6edb898990f426555f052ff2edbee9c41e4dbef7ec20a176eec6164d708e58db9f728e8f18772e4314a5c99cfb89f5178700b989c3acd95fd2f3d02d5f4f7e44498503c4dfaa7cb4412b3a434320d1ad4cb75a5523346f175bf85b0f445b1ccd58f9ecac3fd7f92980d5e7c5a168c5af78a728b77fe9c65dab049c143e7c66368e80f4c4eaade0a640632389446e610bb2bc9d0bd53e22f6fe5540b8feb42e553542d5fcb2cae79ee80a4521f1cefc665991913665d1e04d960aafdc5de9258f3ac1e6a50f473070f80b5975ec0326bafe9f8758d9ea9b3640333e93909e9f55013e50048168"
                                ),
                                new OA\Property(
                                    property: "expires_at",
                                    description: "Date and time of access token expiry",
                                    type: "date",
                                    example: "2025-06-04 13:22:08"
                                ),
                                new OA\Property(
                                    property: "user",
                                    description: "User account information",
                                    properties: [
                                        new OA\Property(property: "id", description: "User ID", type: "string", example: "1"),
                                        new OA\Property(property: "name",description: "User name", type: "string", example: "John Doe"),
                                        new OA\Property(property: "email",description: "User email", type: "string", example: "johndoe@example.com"),
                                    ],
                                    type: "object",
                                ),
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
                        new OA\Property(property: 'message',description: "Human friendly description of the process result", type: 'string', example: 'Client not found!'),
                        new OA\Property(
                            property: "errors",
                            description: "Array of more detailed error messages",
                            type: "array",
                            items: new OA\Items(
                                type: "string",
                                example: "Invalid client details"
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function refreshToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required',
        ]);

        if ($validator->fails()) {

            return $this->error("Validation failed!",[$validator->errors()->first()],400);
        }

        $publicKey = file_get_contents(storage_path('oauth-public.key'));

        $decoded = JWT::decode($request->refresh_token, new Key($publicKey, 'RS256'));
        $decoded = @$decoded->{0};

        $tokenRecord = ProfileToken::with(['profile'])->where('refresh_token', $decoded)->first();

        if (!$tokenRecord) {
            return $this->error("Invalid refresh token",['Invalid client details'],400);
        }

        $response = generate_tokens($tokenRecord->profile);
        $tokenRecord->delete();

        $user = User::find($tokenRecord->profile->user_id);

        if(array_key_exists('access_token', $response)){
            $data = [
                'token_type'   => 'Bearer',
                'access_token' => $response['access_token'],
                'refresh_token' => $response['refresh_token'],
                'expires_at'   => Carbon::parse($response['expires_at'])->format('Y-m-d H:i:s'),
                'user'         => $user
            ];
            return $this->success($data,'Client token generated successfully',default_meta());
        }

        return $this->error("Client token could not be generated",['Invalid credentials supplied'],404);
    }
}
