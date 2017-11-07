<?php
/**
 * Created by PhpStorm.
 * User: Lee
 * Date: 2017/11/07
 * Time: 下午 2:27
 */

namespace Ksd\IPassPay\Parameter;

class BindPayReqParameter
{
    /**
     * laravel request 參數處理
     * @param $request
     */
    public function laravelRequest($request)
    {
        $this->request($request->all());
    }

    /**
     * 參數處理
     * @param $parameters
     */
    private function request($parameters)
    {

    }
}
