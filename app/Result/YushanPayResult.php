<?php

/**
 * User: water
 * Date: 2020/03/18
 * Time: 上午 11:55
 */

namespace App\Result;

use Hashids\Hashids;


class YushanPayResult
{

    public function __construct()
    {
    }


    //生成玉山Pay提交訂單的 query string  |
    public function genConfirmQueryString($orderNo, $amount, $source = 'Citypass')
    {
        //Hash orderNo+amount
        $paymentInfo = array($orderNo, $amount);
        $hashPaymentInfo = (new Hashids('yushanpay', 20))->decode($paymentInfo);
        $confirmQueryString = '?data=' .  $hashPaymentInfo . '&source=' . $source;
        return  $confirmQueryString;
    }
}
