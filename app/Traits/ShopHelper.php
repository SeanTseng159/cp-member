<?php
/**
 * User: lee
 * Date: 2018/12/19
 * Time: 上午 9:42
 */

namespace App\Helpers;

use App\Config\BaseConfig;

class CommonHelper
{
    /**
     * 取前端Domain
     * @return string
     */
    public static function getWebHost($url = '')
    {
        if (env('APP_ENV') === 'production') $host = BaseConfig::WEB_HOST;
        elseif (env('APP_ENV') === 'beta') $host = BaseConfig::WEB_HOST_BETA;
        else $host = BaseConfig::WEB_HOST_TEST;

        return $host . $url;
    }

    /**
     * 取前端Domain
     * @param string $url
     * @return string
     */
    public static function getBackendHost($url = '')
    {
        if (env('APP_ENV') === 'production') $host = BaseConfig::BACKEND_HOST;
        elseif (env('APP_ENV') === 'beta') $host = BaseConfig::BACKEND_HOST_BETA;
        else $host = BaseConfig::BACKEND_HOST_TEST;

        return $host . $url;
    }
    public static function getAdHost($url = '')
    {
        if (env('APP_ENV') === 'production') $host = BaseConfig::AD_HOST;
        elseif (env('APP_ENV') === 'beta') $host = BaseConfig::AD_HOST_BETA;
        else $host = BaseConfig::AD_HOST_TEST;

        return $host . $url;
    }
}
