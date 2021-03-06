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
        if (!$result) return $default;

        if (is_array($result) && array_key_exists($key, $result)) {
            if ($result[$key]) {
                return $result[$key];
            } else {
                return (!is_null($default)) ? $default : $this->changeNullType($result[$key]);
            }
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
            if (is_array($value)) {
                $array[$key] = $this->replaceNullToEmptyString($value);
            } else if(is_object($value) && isset($array->$key)){
                $array->$key = $this->replaceNullToEmptyString($value);
            }

            if (is_null($value)) {
                if (is_array($array)) $array[$key] = '';
                elseif (is_object($array)) $array->$key = '';
            }
        }

        return $array;
    }

    /**
     * 將空值轉換成對應型別空值
     * @param array $parameters
     * @return $this
     */
    public function changeNullType($val = null)
    {
        switch (gettype($val)) {
            case 'boolean':
                return false;
            case 'integer':
            case 'double':
                return 0;
            case 'string':
                return '';
            case 'object':
                return $val;
            case 'array':
                return $val;
            default:
                return null;
        }

        return null;
    }
}
