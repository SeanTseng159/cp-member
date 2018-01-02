<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/26
 * Time: 下午 04:57
 */

namespace Ksd\Mediation\CityPass;
use GuzzleHttp\Exception\ClientException;
use Ksd\Mediation\Helper\EnvHelper;
use Log;

class Service extends Client
{
    use EnvHelper;

    /**
     * 取得常用問題
     * @return array
     */
    public function qa()
    {
        $path = "service/qa/";

        $response = $this->request('GET', $path);
        $body = $response->getBody();
        $result = json_decode($body, true);

        return ($result['statusCode'] === 200) ? $result['data'] : null;

    }


    /**
     * 問題與建議
     * @param $parameters
     * @return bool
     */
    public function suggestion($parameters)
    {
        $response = $this->putParameters($parameters)
            ->request('POST', 'service/suggestion');
        $result = json_decode($response->getBody(), true);

        return ($result['statusCode'] === 201);
    }


}
