<?php
/**
 * User: lee
 * Date: 2018/03/15
 * Time: 上午 9:42
 */

namespace App\Traits;

trait StringHelper
{
    /**
     * 手機隱碼處理
     * @param id $id
     * @return boolean
     */
    public function hidePhoneNumber($phoneNumber)
    {
        $mid = ceil(strlen($phoneNumber) / 2);
        $front = substr($phoneNumber, 0, $mid - 2);
        $end = substr($phoneNumber, $mid + 1);

        return $front . '***' . $end;
    }

    /**
     * 姓名隱碼處理
     * @param id $id
     * @return boolean
     */
    public function hideName($name)
    {
        if (!$name) return $name;

        $strLen = mb_strlen($name);
        $mid = ceil($strLen / 2);

        if ($strLen === 2) {
            $name = mb_substr($name, 0, 1) . '*';
        }
        else if ($strLen === 3) {
            $name = mb_substr($name, 0, $mid - 1) . '*' . mb_substr($name, $mid);
        }
        else if ($strLen === 4) {
            $name = mb_substr($name, 0, $mid - 1) . '**' . mb_substr($name, $mid + 1);
        }
        else if ($strLen >= 5) {
            $front = mb_substr($name, 0, $mid - 2);
            $end = mb_substr($name, $mid + 1);
            $name = $front . '***' . $end;
        }

        return $name;
    }

    /**
     * 姓名隱碼處理
     * @param id $id
     * @return boolean
     */
    public function hideAddress($address)
    {
        $strLen = mb_strlen($address);

        if ($strLen >= 5) {
            $hideLen = $strLen - 3;
            $end = mb_substr($address, $strLen - 3);
            $address = '';
            for ($i = 0; $i < $hideLen; $i ++) $address .= '*';
            $address .= $end;
        }
        else {
            $address = $this->hideName($address);
        }

        return $address;
    }

    /**
     * 姓名隱碼處理
     * @param id $id
     * @return boolean
     */
    public function outputStringLength($str = '', $length = 50)
    {
        return mb_substr(strip_tags($str), 0, 50);
    }
}
