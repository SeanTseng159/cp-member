<?php
/**
 * Created by PhpStorm.
 * User: Lee
 * Date: 2017/11/07
 * Time: 下午 2:27
 */

namespace Ksd\IPassPay\Parameter;

class CallbackParameter
{
    public $platform;
    public $source;
    public $orderId;

    /**
     * laravel request 參數處理
     * @param $request
     */
    public function laravelRequest($request)
    {
        foreach ($request->all() as $key => $value) {
            $this->{$key} = $value;
        }

        $session = $request->session()->pull('ipassPay', 'default');
        foreach ($session as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
