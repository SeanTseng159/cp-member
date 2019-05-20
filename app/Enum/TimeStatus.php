<?php
/**
 * Created by PhpStorm.
 * User: Annie
 * Date: 2019/3/11
 * Time: 下午 12:05
 */


namespace App\Enum;

use BenSampo\Enum\Enum;
use Carbon\Carbon;


final class TimeStatus extends Enum
{
    const ERROR = 0; // 時間錯誤
    const YET = 1; //未開始
    const PROCESSING = 2; //進行中
    const EXPIRED = 3;// 已過期

    public static function checkStatus($startTime, $endTime)
    {
        $ret = self::ERROR;
        $startTime = Carbon::parse($startTime);
        $endTime = Carbon::parse($endTime);

        $now = Carbon::now();
        if ($startTime->gte($endTime))
            $ret = self::ERROR;
        else if ($now->lessThan($startTime)) {
            $ret = self::YET;
        } else if ($now->between($startTime, $endTime)) {
            $ret = self::PROCESSING;
        } else if ($now->gt($endTime)) {
            $ret = self::EXPIRED;
        }
        return $ret;
    }
}