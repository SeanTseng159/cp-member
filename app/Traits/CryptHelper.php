<?php
/**
 * User: lee
 * Date: 2017/09/26
 * Time: 上午 9:42
 */

namespace App\Traits;

use Crypt;

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

    public function encryptHashId($perfix = '', $id = 0)
    {
    	if (!$perfix) $perfix = time();

    	return Crypt::encrypt($perfix . '__' . $id);
    }

    public function decryptHashId($perfix = '', $code)
    {
    	if (!$code) return 0;

    	$decode = Crypt::decrypt($code);

    	return str_replace($perfix . '__', '', $decode);
    }
}
