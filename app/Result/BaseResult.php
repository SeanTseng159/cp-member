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

	public $backendHost;

    public function __construct()
    {
        if (env('APP_ENV') === 'production') {
            $this->backendHost = BaseConfig::BACKEND_HOST;
        }
        elseif (env('APP_ENV') === 'beta') {
            $this->backendHost = BaseConfig::BACKEND_HOST_BETA;
        }
        else {
            $this->backendHost = BaseConfig::BACKEND_HOST_TEST;
        }
    }
}
