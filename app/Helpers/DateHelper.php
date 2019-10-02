<?php
/**
 * User: lee
 * Date: 2019/01/14
 * Time: 上午 9:42
 */

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    public static function format(String $datetime, String $format)
    {
        return Carbon::parse($datetime)->format($format);
    }

    public static function chinese(String $datetime, String $format)
    {
        setlocale(LC_TIME, 'cht');
        $carbon = new Carbon;
        $carbon->setLocale('zh_TW.utf8');

        $time = Carbon::parse($datetime)->formatLocalized($format);
        $time = iconv('GBK', 'UTF-8', $time);
        return $time;
    }

}

