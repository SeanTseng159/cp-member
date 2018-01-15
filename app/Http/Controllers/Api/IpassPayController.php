<?php
/**
 * User: Lee
 * Date: 2017/10/27
 * Time: 下午2:20
 */

namespace Ksd\IPassPay\Http\Controllers;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\IPassPay\Services\PayService;
use Ksd\IPassPay\Parameter\PayParameter;
use Ksd\IPassPay\Parameter\RefundParameter;
use Carbon;
use Log;

class IpassPayController extends RestLaravelController
{
    protected $service;

    const MAGENTO = 'magento';
    const CITYPASS = 'ct_pass';

    public function __construct(PayService $service)
    {
        $this->service = $service;
    }

    /**
     * refund
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refund(Request $request)
    {
      $parameter = (new PayParameter)->bindRefund($request);

      if (!$parameter) return $this->failure('E0001', '傳送參數錯誤');

      try {
        $result = $this->service->bindRefund($parameter);

        Log::debug('=== ipass pay refund back ===');
        Log::debug(print_r($result, true));

        if (!$result['status']) return $this->failure('E0102', '訂單退款失敗');

        //成功
        $callbackParameter = (new RefundParameter)->callbackParameter($result['data']);
        return $this->success($callbackParameter);
      }
      catch (Exception $e) {
        Log::debug('=== ipass pay refund error ===');
        Log::debug(print_r($e, true));

        return $this->failure('E0102', '訂單退款失敗');
      }
    }
}
