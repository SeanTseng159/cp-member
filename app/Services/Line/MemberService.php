<?php

namespace App\Services\Line;

use App\Repositories\Line\MemberRepository;
use GuzzleHttp\Client;

class MemberService
{

    protected $repository;

    public function __construct(MemberRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 取得登入Url
     * @param $data
     * @return mixed
     */
    public function loginUrl($platform)
    {
      $client_id = config('social.line.channel_id');
      $redirect_url = route('line.memberCallback') .'/'. $platform;
      $url = 'https://access.line.me/oauth2/v2.1/authorize?response_type=code&client_id=' . $client_id . '&redirect_uri=' . $redirect_url . '&state=citypass&scope=openid%20profile%20email&nonce=citypassksd';

      return $url;
    }

    /**
     * 取access_token
     * @param $code,$platform
     * @return mixed
     */
    public function accessToken($code, $platform)
    {
      $client = new Client();
      $response = $client->request('POST', 'https://api.line.me/oauth2/v2.1/token', [
          'form_params' => [
              'grant_type' => 'authorization_code',
              'code' => $code,
              'redirect_uri' => route('line.memberCallback') .'/'. $platform,
              'client_id' => config('social.line.channel_id'),
              'client_secret' => config('social.line.channel_secret')
          ]
      ]);

      return json_decode($response->getBody()->getContents());
    }

    /**
     * 取得會員資料
     * @param $data
     * @return mixed
     */
    public function member($parameters)
    {
        return $this->repository->member($parameters);
    }

    /**
     * 會員登出
     * @param $data
     * @return mixed
     */
    public function logout($parameters)
    {
        return $this->repository->logout($parameters);
    }

}
