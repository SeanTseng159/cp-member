<?php
/**
 * User: Lee
 * Date: 2017/11/07
 * Time: 下午2:20
 */

namespace Ksd\IPassPay\Services;

use Ksd\IPassPay\Core\Client\BaseClient;

class IPassPayService extends BaseClient
{
    public function bindPayReq($parameters)
    {
        $response = $this->putParameters($parameters)
            ->request('POST', 'BindPayReq');
        return json_decode($response->getBody(), true);
    }
}
