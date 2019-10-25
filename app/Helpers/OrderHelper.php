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


    public function getMergeStatusCode($code)
    {
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

    public function getStatusString($code)
    {
        return $this->getOrderStatus($this->getMergeStatusCode($code));
    }


}
