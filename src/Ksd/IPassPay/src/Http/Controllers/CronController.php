<?php
/**
 * User: Lee
 * Date: 2017/10/27
 * Time: 下午2:20
 */

namespace Ksd\IPassPay\Http\Controllers;

use Illuminate\Http\Request;
use Ksd\IPassPay\Core\Controller\RestLaravelController;
use Ksd\IPassPay\Services\PayService;
use Log;

class CronController extends RestLaravelController
{
    protected $lang;
    protected $service;

    public function __construct(PayService $service)
    {
      $this->service = $service;
    }

    /**
     * payResult
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function payResult(Request $request)
    {
      #todo
      #去資料庫查未繳費訂單，在送ipasspay查詢
    }
}
