<?php
/**
 * User: Lee
 * Date: 2017/11/07
 * Time: 下午2:20
 */

namespace Ksd\IPassPay\Helper;

trait ObjectHelper
{

    public function parseQueryString($queryString = '') {
        if (!$queryString) return;
        parse_str($queryString, $queryArray);
        if (!is_array($queryArray)) return;
        $parameters = new \stdClass;
        foreach ($queryArray as $key => $value) {
            $parameters->{$key} = $value;
        }

        return $parameters;
    }
}
