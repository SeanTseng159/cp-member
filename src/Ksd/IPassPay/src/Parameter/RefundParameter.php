<?php
/**
 * Created by PhpStorm.
 * User: Lee
 * Date: 2017/11/07
 * Time: 下午 2:27
 */

namespace Ksd\IPassPay\Parameter;

class RefundParameter
{
    /**
     * 將ipasspay退款回傳資料整合
     * @param $callback
     * @param $orderNo
     */
    public function callbackParameter($callback)
    {
        unset($callback->rtnCode);
        unset($callback->rtnMsg);
        unset($callback->client_id);
        unset($callback->version);
        unset($callback->timestamp);
        unset($callback->signature);

        return $callback;
    }
}
