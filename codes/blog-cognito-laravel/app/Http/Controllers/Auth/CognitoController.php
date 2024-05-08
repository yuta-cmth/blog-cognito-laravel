<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\CognitoUser;

class CognitoController extends Controller
{
    public function loginCb(Request $request)
    {
        Log::info('login cb');
        $code = $request->query()['code'];
        $token_endpoint = 'https://' . config('aws.cognito.hosted_ui_domain') . '/oauth2/token';
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $token_endpoint, [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => config('aws.cognito.client_id'),
                'client_secret' => config('aws.cognito.client_secret'),
                'redirect_uri' => route('cognito.login-cb'),
            ],
        ]);
        $body = $response->getBody();
        $body = json_decode($body, true);
        $id_token = $body['id_token'];

        $payload = explode('.', $id_token)[1];
        $payload = base64_decode($payload);
        $payload = json_decode($payload, true);
        Log::debug('token');
        Log::debug($payload);
        $username = $payload['cognito:username'];
        $refresh_token = $body['refresh_token'];

        session()->put('username', $username);
        CognitoUser::upsert(
            [
                'username' => $username,
                'refresh_token' => $refresh_token,
            ],
            uniqueBy: ['username'],
            update: ['refresh_token'],
        );
        Log::debug('Upserted user');

        return redirect()->route('home');
    }

    public function logoutCb(Request $request)
    {
        $request->session()->forget('username');
        return redirect()->route('home');
    }
}
