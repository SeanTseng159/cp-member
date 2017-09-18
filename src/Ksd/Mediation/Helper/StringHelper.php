<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/8
 * Time: 下午 3:17
 */

namespace Ksd\Mediation\Helper;


trait StringHelper
{
    /**
     * 字數擷取
     * @param $str
     * @param int $l
     * @return array
     */
    protected function str_split_unicode($str, $l = 0) {
        if ($l > 0) {
            $ret = array();
            $len = mb_strlen($str, "UTF-8");
            for ($i = 0; $i < $len; $i += $l) {
                $ret[] = mb_substr($str, $i, $l, "UTF-8");
            }
            return $ret;
        }
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }
}