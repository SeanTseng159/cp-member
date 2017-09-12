<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/18
 * Time: 上午 9:32
 */

namespace Ksd\Mediation\Helper;


trait ObjectHelper
{
    public function arrayDefault($result, $key , $default = '')
    {
        if (empty($result)) {
            return $default;
        }
        return array_key_exists($key, $result) ? $result[$key] : $default;
    }

    public function customAttributes($attributes, $key)
    {
        foreach ($attributes as $attribute) {
            if($attribute['attribute_code'] === $key) {
                return $attribute['value'];
            }
        }
        return '';
    }
}