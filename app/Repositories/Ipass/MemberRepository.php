<?php
/**
 * User: lee
 * Date: 2017/10/26
 * Time: 上午 9:42
 */

namespace App\Repositories\Ipass;

use GuzzleHttp\Client as GuzzleHttpClient;
use App\Core\BaseClient;

class MemberRepository extends BaseClient
{

    public function __construct()
    {
        $this->baseUrl = env('IPASS_OAUTH_PATH');
        $this->client = new GuzzleHttpClient([
            'base_uri' => $this->baseUrl
        ]);
    }

    /**
     * 取得授權code
     * @param $parameters
     * @return mixed
     */
    public function authorize($parameters)
    {
        $response = $this->putParameters($parameters)
            ->request('POST', 'auth/authorize');
        $data = $response->getBody()->getContents();

        return json_decode($data);
    }

    /**
     * 取得會員資料
     * @param $parameters
     * @return mixed
     */
    public function member($parameters)
    {
        $response = $this->putParameters($parameters)
            ->request('POST', 'auth_member/single/' . $parameters->member_id);
        $data = $response->getBody()->getContents();

        return json_decode($data);
    }
}
