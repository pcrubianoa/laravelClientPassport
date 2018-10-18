<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client as Guzzle;


class TwitterAuthController extends Controller
{
    protected $client;

    public function __construct(Guzzle $client)
    {
        $this->client = $client;
    }

    public function redirect()
    {
        $query = http_build_query([
            'client_id' => '9',
            'redirect_uri' => 'http://laravelclientpassport.test/auth/twitter/callback',
            'response_type' => 'code',
            'scope' => 'view-tweets'
        ]);

        return redirect('http://laravelauthenticationpassport.test/oauth/authorize?' . $query);
    }

    public function callback(Request $request)
    {
        $response = $this->client->post('http://laravelauthenticationpassport.test/oauth/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => '9',
                'client_secret' => 'm23EW9H0zxjoeVEkUP00CgGwigWHWrX8AQAfx7Tl',
                'redirect_uri' => 'http://laravelclientpassport.test/auth/twitter/callback',
                'code' => $request->code
            ]
        ]);

        $response = json_decode($response->getBody());
        
        $request->user()->token()->delete();

        $request->user()->token()->create([
            'access_token' => $response->access_token,
            'refresh_token' => $response->refresh_token,
            'expires_in' => $response->expires_in
        ]);

        return redirect('/home');
    }

    public function refresh(Request $request)
    {
        $response = $this->client->post('http://laravelauthenticationpassport.test/oauth/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $request->user()->token->refresh_token,
                'client_id' => '9',
                'client_secret' => 'm23EW9H0zxjoeVEkUP00CgGwigWHWrX8AQAfx7Tl',
                'scope' => 'view-tweets',
            ]
        ]);

        $response = json_decode($response->getBody());

        $request->user()->token()->update([
            'access_token' => $response->access_token,
            'expires_in' => $response->expires_in,
            'refresh_token' => $response->refresh_token,
        ]);

        return redirect()->back();
    }
}
