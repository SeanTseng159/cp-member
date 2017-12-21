<?php
/**
 * User: Lee
 * Date: 2017/11/07
 * Time: 下午2:20
 */

namespace Ksd\IPassPay\Repositories;

use Ksd\IPassPay\Core\Client\BaseClient;
use Ksd\IPassPay\Helper\ObjectHelper;

class PayRepository extends BaseClient
{
    use ObjectHelper;

    /**
     * EC平台請求支付Token (步驟一)
     * @param $parameters
     * @return mixed
     */
    public function bindPayReq($parameters)
    {
        $response = $this->putParameters($parameters)
            ->request('POST', 'api/BindPayReq');
        $responseQueryString = urldecode($response->getBody()->getContents());
        return $this->parseQueryString($responseQueryString);
    }

    /**
     * 支付確認 (最後步驟)
     * @param $parameters
     * @return mixed
     */
    public function bindPayStatus($parameters)
    {
        $response = $this->putParameters($parameters)
            ->request('POST', 'api/bindPayStatus');
        $responseQueryString = urldecode($response->getBody()->getContents());
        return $this->parseQueryString($responseQueryString);
    }
}
