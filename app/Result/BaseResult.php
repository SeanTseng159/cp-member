<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Result;

use App\Helpers\CommonHelper;
use App\Traits\ObjectHelper;

class BaseResult
{
	use ObjectHelper;

    public $webHost;
	public $backendHost;

    public function __construct()
    {
        $this->webHost = CommonHelper::getWebHost();
        $this->backendHost = CommonHelper::getBackendHost();
    }
}
