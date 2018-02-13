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
use Ksd\IPassPay\Services\IpasspayLogService;
use Ksd\Mediation\Services\OrderService;
use Ksd\IPassPay\Parameter\PayParameter;
use Ksd\IPassPay\Parameter\NotifyParameter;
use Ksd\IPassPay\Parameter\RefundParameter;
use Ksd\IPassPay\Parameter\ResultParameter;
use Carbon;
use Log;

class IpassPayController extends RestLaravelController
{
    protected $service;
    protected $logService;
    protected $orderService;

    public function __construct(PayService $service, IpasspayLogService $logService, OrderService $orderService)
    {
        $this->service = $service;
        $this->logService = $logService;
        $this->orderService = $orderService;
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
        $status = 0;

        if ($request->has(['order_id', 'amt', 'pay_time'])) {
            try {
                $notifyParameter = new NotifyParameter;
                $parameter = $notifyParameter->laravelRequest($request);
                $order = $this->logService->findByOrderId($parameter->order_id);

                if ($order) {
                    // 更新訂單
                    $updateParameter = $notifyParameter->updateParameter($parameter, $order);
                    $updateResult = $this->orderService->updateByIpasspayATM($updateParameter);

                    Log::debug('=== atm update order ===');
                    Log::debug(print_r($updateResult, true));

                    // 成功
                    if ($updateResult) $status = 1;
                }
            } catch (Exception $e) {
                Log::debug('=== ipass pay notify error ===');
            }
        }

        $parameter = (new PayParameter)->bindPayNotify($status);
        $res = urlencode(http_build_query((array) $parameter));

        return response($res, 200)
                    ->header('Content-Type', 'application/x-www-form-urlencoded')
                    ->header('Cache-Control', 'no-store')
                    ->header('Pragma', 'no-cache');
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

            if (!$result['status']) return ($result['data']) ? $this->failure('E9999', $result['data']->rtnMsg) : $this->failure('E9999', '交易結果查詢錯誤');

            //成功
            $callbackParameter = (new ResultParameter)->callbackParameter($result['data']);
            return $this->success($callbackParameter);
        } catch (Exception $e) {
            Log::debug('=== ipass pay result error ===');
            return $this->failure('E9999', '交易結果查詢錯誤');
        }
    }
}
