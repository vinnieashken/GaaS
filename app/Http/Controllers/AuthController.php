<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\Response;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Client;


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



}
