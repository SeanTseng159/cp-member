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
        $this->callback = new \stdClass;
        foreach ($request->all() as $key => $value) {
            $this->callback->{$key} = $value;
        }

        $session = session('ipassPay');
        if ($session) {
            foreach ($session as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }
}
