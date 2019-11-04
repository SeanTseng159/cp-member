<?php
/**
 * User: lee
 * Date: 2018/12/19
 * Time: 上午 9:42
 */

namespace App\Traits;

use App\Config\BaseConfig;

trait ShopHelper
{
    function getWaitNoString($value)
    {
        return $value;
//        return sprintf("%04d", $value);
    }
}
