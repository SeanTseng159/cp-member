<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Traits;

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
     * 給預設值
     * @param $result
     * @param $key
     * @param string $default
     * @return string
     */
    public function default($val , $default = null)
    {
        return (!is_null($default)) ? $default : $this->changeNullType($val);
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

    /**
     * 資料依日期做排序
     * @param $arr
     * @param$key
     * @param $type
     * @param $short
     * @return array
     */
    public function multiArraySort($arr, $key , $type = SORT_REGULAR, $short = SORT_DESC)
    {
        if (!empty($arr)) {
            foreach ($arr as $k => $v) {
                $name[$k] = $v[$key];
            }
            array_multisort($name, $type, $short, $arr);
        }
        return $arr;
    }
}
