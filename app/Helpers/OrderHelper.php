<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 9:42
 */

namespace App\Helpers;


use App\Config\Ticket\OrderConfig;


Class OrderHelper
{


    public function getStatusCode($orderStatus, $orderPayMethod, $atmVirtualAccount, $payAmount)
    {


        $isRepay = false;

        $orderPayMethod = $orderPayMethod ?: 0;

        if (OrderConfig::PAYMENT_METHOD[$orderPayMethod] === 'atm') {
            if ($payAmount != 0) {
                $isRepay = empty($atmVirtualAccount);
            } else {
                $isRepay = false;
            }
        } else {
            $isRepay = ($orderStatus === 0) ? true : false;
        }
        $code = $this->changeStatusCode($orderStatus,$isRepay);


        $mergeCode = '';

        if ($code === '10') $mergeCode = '01';
        else if ($code === '00' || $code === '01') $mergeCode = '00';
        else if ($code === '23') $mergeCode = '02';
        else if ($code === '24') $mergeCode = '03';
        else if ($code === '20' || $code === '21' || $code === '22') $mergeCode = '04';
        else if ($code === '02') $mergeCode = '08';
        else if ($code === '03') $mergeCode = '07';

        return $mergeCode;
    }

    /**
     * 訂單狀態
     * @param $mergeCode
     * @return string
     */
    public function getOrderStatus($mergeCode)
    {
        return trans('ticket/order.status.' . OrderConfig::STATUS[$mergeCode]);
    }

    private function changeStatusCode($code, $isRePay = false)
    {
        if ($code === 0 && $isRePay) $code = 3;

        switch ($code) {
            case 0:
                return '00';
            case 1:
                return '01';
            case 2:
                return '02';
            case 3:
                return '03';
            case 10:
                return '10';
            case 20:
                return '20';
            case 21:
                return '21';
            case 22:
                return '22';
            case 23:
                return '23';
            case 24:
                return '24';
        }

        return '02';
    }

}
