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
    /**
     * 陣列根據 key 取得值, 若無給預設值
     * @param $result
     * @param $key
     * @param string $default
     * @return string
     */
    public function arrayDefault($result, $key , $default = '')
    {
        if (empty($result)) {
            return $default;
        }
        return array_key_exists($key, $result) ? $result[$key] : $default;
    }

    /**
     * magento 根據 key 取得產品額外欄位
     * @param $attributes
     * @param $key
     * @param $default
     * @return string
     */
    public function customAttributes($attributes, $key, $default = '')
    {
        foreach ($attributes as $attribute) {
            if($attribute['attribute_code'] === $key) {
                return $attribute['value'];
            }
        }
        return $default;
    }
}