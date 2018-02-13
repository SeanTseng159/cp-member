<?php
/**
 * Created by PhpStorm.
 * User: Lee
 * Date: 2017/11/07
 * Time: 下午 2:27
 */

namespace Ksd\IPassPay\Parameter;

class NotifyParameter
{
    /**
     * laravel request 參數處理
     * @param $request
     */
    public function laravelRequest($request)
    {
        $parameter = new \stdClass;
        $parameter->client_id = $request->input('client_id');
        $parameter->order_id = $request->input('order_id');
        $parameter->amt = $request->input('amt');
        $parameter->pay_time = $request->input('pay_time');
        $parameter->timestamp = $request->input('timestamp');
        $parameter->signature = $request->input('signature');

        return $parameter;
    }

    /**
     * 將ipasspay回傳資料整合
     * @param $parameter
     * @param $order
     * @return Object
     */
    public function updateParameter($parameter, $order)
    {
        $parameter->source = $order->source;
        $parameter->member_id = $order->member_id;
        $parameter->orderNo = $order->order_no;
        unset($parameter->client_id);
        unset($parameter->timestamp);
        unset($parameter->signature);

        return $parameter;
    }
}
