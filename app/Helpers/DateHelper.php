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
}

