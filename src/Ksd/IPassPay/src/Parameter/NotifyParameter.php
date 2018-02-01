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
        $parameter->client = $request->input('client');
        $parameter->order_id = $request->input('order_id');
        $parameter->amt = $request->input('amt');
        $parameter->pay_time = $request->input('pay_time');
        $parameter->timestamp = $request->input('timestamp');
        $parameter->signature = $request->input('signature');

        return $parameter;
    }
}
