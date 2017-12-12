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
    public function arrayDefault($result, $key , $default = null)
    {
        if (empty($result)) {
            return $default;
        }

        if (array_key_exists($key, $result) && $result[$key]) {
            return $result[$key];
        }

        return $default;
    }

    /**
     * magento 根據 key 取得產品額外欄位
     * @param $attributes
     * @param $key
     * @param $default
     * @return string
     */
    public function customAttributes($attributes, $key, $default = null)
    {
        foreach ($attributes as $attribute) {
            if($attribute['attribute_code'] === $key) {
                return $attribute['value'];
            }
        }
        return $default;
    }

    /**
     * 將array中的空值轉換成空字串
     * @param array $parameters
     * @return $this
     */
    public function replaceNullToEmptyString($array = [])
    {
        foreach ($array as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $array[$key] = $this->replaceNullToEmptyString($value);
            }

            if (is_null($value)) {
                if (is_array($array)) $array[$key] = '';
                elseif (is_object($array)) $array->$key = '';
            }
        }

        return $array;
    }
}
