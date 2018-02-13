<?php
/**
 * User: Lee
 * Date: 2017/10/27
 * Time: 下午2:20
 */

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\IPassPay\Services\PayService;
use Ksd\IPassPay\Parameter\PayParameter;
use Ksd\IPassPay\Parameter\RefundParameter;
use Ksd\IPassPay\Parameter\ResultParameter;
use Carbon;
use Log;

class IpassPayController extends RestLaravelController
{
    protected $service;

    public function __construct(PayService $service)
    {
        $this->service = $service;
    }

    /**
     * refund 退款
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

        if (!$result['status']) return ($result['data']) ? $this->failure('E0102', $result['data']->rtnMsg) : $this->failure('E0102', '訂單退款失敗');

        //成功
        $callbackParameter = (new RefundParameter)->callbackParameter($result['data']);
        return $this->success($callbackParameter);
      }
      catch (Exception $e) {
        Log::debug('=== ipass pay refund error ===');
        return $this->failure('E0102', '訂單退款失敗');
      }
    }

    /**
     * payNotify 入帳通知
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function payNotify(Request $request)
    {
        $parameter = (new PayParameter)->payNotify($request);

        // return response()->json(['rtnCode' => -9999]);
    }

    /**
     * payResult 交易結果查詢
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function result(Request $request)
    {
        $order_id = $request->input('order_id');

        if (!$order_id) return $this->failure('E0001', '傳送參數錯誤');

        try {
            $parameter = (new PayParameter)->bindPayResult($order_id);
            $result = $this->service->bindPayResult($parameter);

            if (!$result['status']) return ($result['data']) ? $this->failure('E9999', $result['data']->rtnMsg) : $this->failure('E9999', '交易結果查詢');

            //成功
            $callbackParameter = (new ResultParameter)->callbackParameter($result['data']);
            return $this->success($callbackParameter);
        } catch (Exception $e) {
            Log::debug('=== ipass pay result error ===');
            return $this->failure('E9999', '交易結果查詢');
        }
    }
}
