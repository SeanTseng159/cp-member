<?php
/**
 * Created by PhpStorm.
 * User: Lee
 * Date: 2017/11/07
 * Time: 下午 2:27
 */

namespace Ksd\IPassPay\Parameter;

class OrderParameter
{
    /**
     * 將ipasspay回傳資料整合
     * @param $callback
     * @param $orderNo
     */
    public function updateParameter($payStatusParameter, $callback)
    {
        $parameter = $payStatusParameter;
        $parameter->orderNo = $callback->orderNo;
        $parameter->source = $callback->source;
        $parameter->paySource = 'ipasspay';
        unset($parameter->rtnCode);
        unset($parameter->rtnMsg);
        unset($parameter->client_id);
        unset($parameter->timestamp);

        return $parameter;
    }
}
