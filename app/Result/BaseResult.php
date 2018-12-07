<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Result;

use App\Config\BaseConfig;

use App\Traits\ObjectHelper;

class BaseResult
{
	use ObjectHelper;

    public $webHost;
	public $backendHost;

    public function __construct()
    {
        if (env('APP_ENV') === 'production') {
            $this->webHost = BaseConfig::WEB_HOST;
            $this->backendHost = BaseConfig::BACKEND_HOST;
        }
        elseif (env('APP_ENV') === 'beta') {
            $this->webHost = BaseConfig::WEB_HOST_BETA;
            $this->backendHost = BaseConfig::BACKEND_HOST;
        }
        else {
            $this->webHost = BaseConfig::WEB_HOST_TEST;
            $this->backendHost = BaseConfig::BACKEND_HOST;
        }
    }
}
