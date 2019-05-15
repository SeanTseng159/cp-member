<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 9:42
 */

namespace App\Helpers;


use Carbon\Carbon;

Class StringHelper
{


    public static function getDate($startDate, $endDate = null, $format = 'Y-m-d')
    {
        if ($endDate) {
            return Carbon::parse($startDate)->format($format) .
                "~" .
                Carbon::parse($endDate)->format($format);
        } else
            return Carbon::parse($startDate)->format($format);
    }


}
