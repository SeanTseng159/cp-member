<?php

namespace App\Services\Line;

use GuzzleHttp\Client;
use Firebase\JWT\JWT;

class MemberService
{

    protected $repository;

    public function __construct()
    {
        // $this->repository = $repository;
    }

    /**
     * 取得登入Url
     * @param $data
     * @return mixed
     */
    public function loginUrl($platform)
    {
        $client_id = env('LINE_CHANNEL_ID');
        $redirect_url = secure_url('line/memberCallback');
        $url = 'https://access.line.me/oauth2/v2.1/authorize?response_type=code&client_id=' . $client_id . '&redirect_uri=' . $redirect_url . '&state=citypass&scope=openid%20profile%20email&nonce=' . $platform;

        return $url;
    }

    /**
     * 取access_token
     * @param $code,$platform
     * @return mixed
     */
    public function accessToken($code)
    {
      $client = new Client();
      $response = $client->request('POST', 'https://api.line.me/oauth2/v2.1/token', [
          'form_params' => [
              'grant_type' => 'authorization_code',
              'code' => $code,
              'redirect_uri' => secure_url('line/memberCallback'),
              'client_id' => env('LINE_CHANNEL_ID'),
              'client_secret' => env('LINE_SECRET')
          ]
      ]);

      return json_decode($response->getBody()->getContents());
    }

    /**
     * 取得會員資料
     * @param $data
     * @return mixed
     */
    public function getUserProfile($token)
    {
        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];
        $response = $client->request('GET', 'https://api.line.me/v2/profile', [
            'headers' => $headers
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * 解析JWT取得payload
     * @param $data
     * @return mixed
     */
    public function getPayload($id_token)
    {
      return JWT::decode($id_token, env('LINE_SECRET'), ['HS256']);
    }

    /**
     * 會員登出
     * @param $data
     * @return mixed
     */
    public function logout($parameters)
    {
        // return $this->repository->logout($parameters);
    }

}
