<?php
/**
 * User: lee
 * Date: 2017/09/26
 * Time: 上午 9:42
 */

namespace App\Traits;

trait CryptHelper
{
    public function base64UrlEncode($str = '')
    {
        return strtr(base64_encode($str), '+/=', '._-');
    }

    public function base64UrlDecode($str = '')
    {
        return base64_decode(strtr($str, '._-', '+/='));
    }

}
