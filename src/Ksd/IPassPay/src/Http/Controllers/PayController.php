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
use Ksd\IPassPay\Services\IpasspayLogService;
use App\Services\MemberService;
use Ksd\Mediation\Services\OrderService;
use Ksd\IPassPay\Parameter\PayParameter;
use Ksd\IPassPay\Parameter\LogParameter;
use Ksd\IPassPay\Parameter\CallbackParameter;
use Ksd\IPassPay\Parameter\OrderParameter;
use Carbon;
use Log;

class PayController extends RestLaravelController
{
    protected $lang;
    protected $service;
    protected $logService;
    protected $memberService;
    protected $orderService;

    const MAGENTO = 'magento';
    const CITYPASS = 'ct_pass';

    public function __construct(PayService $service, IpasspayLogService $logService, MemberService $memberService, OrderService $orderService)
    {
        $this->service = $service;
        $this->logService = $logService;
        $this->memberService = $memberService;
        $this->orderService = $orderService;

        $this->lang = env('APP_LANG');
    }

    /**
     * ipass pay
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pay(Request $request)
    {
      $parameter = new PayParameter;
      $parameter->laravelRequest($request);

      Log::debug('=== ipass pay 前端送過來的值 ===');
      Log::debug(print_r($parameter, true));

      // 檢查會員
      $result = $this->memberService->checkToken($parameter->token, $parameter->platform);
      if (!$result) {
        Log::debug('=== ipass pay 會員驗證失效 ===');
        return $this->failureRedirect($parameter);
      }

      //Log寫入DB
      $logParameter = new LogParameter;
      $log = $logParameter->pay($request);
      $this->logService->create($log);

      // EC平台請求支付Token (步驟一)
      try {
        Log::debug('=== ipass pay 訂單查詢 ===');
        $order = $this->orderService->findOneByIpassPay($parameter);
        if (!$order) {
          Log::debug('=== ipass pay 訂單不存在 ===');
          return $this->failureRedirect($parameter);
        }

        $bindPayParameter = $parameter->bindPayReq($order);
        $result = $this->service->bindPayReq($bindPayParameter);

        if (!$result['status']) {
          return $this->failureRedirect($parameter);
        }

        // 取得Token到ipass pay 付款介面 (步驟二)
        $bindPayParameter = $parameter->bindPayToken($result['data']);

        return view('ipass::pay', ['parameter' => $bindPayParameter]);
      }
      catch (\Exception $e) {
        Log::debug('=== ipass pay error ===');
        Log::debug(print_r($e, true));

        return $this->failureRedirect($parameter);
      }
    }

    public function successCallback(Request $request)
    {
      $callbackParameter = new CallbackParameter;
      $callbackParameter->laravelRequest($request);

      //Log寫入DB
      $this->logService->update($callbackParameter->callback->order_id, ['bindPayCallback' => json_encode($request->all())]);

      // 跟ipass確認付款
      $payParameter = new PayParameter;
      $bindPayStatusParameter = $payParameter->bindPayStatus($callbackParameter->callback);
      $payStatusResult = $this->service->bindPayStatus($bindPayStatusParameter);

      // 失敗導回前端
      if (!$payStatusResult['status']) return $this->failureRedirect($callbackParameter);

      $orderParameter = new OrderParameter;
      $updateOrderParameter = $orderParameter->updateParameter($payStatusResult['data'], $callbackParameter);

      //Log寫入DB
      $this->logService->update($updateOrderParameter->order_id, ['pay_type' => $updateOrderParameter->payment_type, 'pay_status' => ($updateOrderParameter->status === 'Y')]);
      // 送後端訂單更新
      $updateResult = $this->orderService->update($callbackParameter->token, $updateOrderParameter);

      Log::debug('=== update order ===');
      Log::debug(print_r($updateResult, true));

      // 導回前端
      return ($updateResult) ? $this->successRedirect($callbackParameter) : $this->failureRedirect($callbackParameter);
    }

    public function failureCallback(Request $request)
    {
      Log::debug('=== ipass pay failure callback ===');
      Log::debug(print_r($request->all(), true));

      $callbackParameter = new CallbackParameter;
      $callbackParameter->laravelRequest($request);

      //Log寫入DB
      $this->logService->update($callbackParameter->callback->order_id, ['bindPayCallback' => json_encode($request->all())]);

      //撈訂單詳細重新加入購物車
      if ($callbackParameter->source === SELF::MAGENTO) {
        $parameter = new \stdClass;
        $parameter->source = $callbackParameter->source;
        $parameter->id = $callbackParameter->orderNo;
        $parameter->itemId = null;
        $this->orderService->find($parameter);
      }

      return $this->failureRedirect($callbackParameter);
    }

    private function successRedirect($parameter)
    {
      $url = env('CITY_PASS_WEB') . $this->lang;

      if ($parameter->platform === 'app') {
        $url = 'app://order?id=' . $parameter->orderNo . '&source=' . $parameter->source . '&result=true&msg=success';

        return '<script>location.href="' . $url . '";</script>';
      }
      else {
        $s = ($parameter->source === SELF::CITYPASS) ? 'c' : 'm';
        $url = env('CITY_PASS_WEB') . $this->lang . '/checkout/complete/' . $s . '/' . $parameter->orderNo;

        return redirect($url);
      }
    }

    private function failureRedirect($parameter)
    {
      if ($parameter->platform === 'app') {
        $url = 'app://order?id=' . $parameter->orderNo . '&source=' . $parameter->source . '&result=false&msg=failure';

        return '<script>location.href="' . $url . '";</script>';
      }
      else {
        $s = ($parameter->source === SELF::CITYPASS) ? 'c' : 'm';

        $url = env('CITY_PASS_WEB') . $this->lang . '/checkout/complete/' . $s . '/' . $parameter->orderNo;

        if ($parameter->source === SELF::MAGENTO) {
          $url = env('CITY_PASS_WEB') . $this->lang . '/checkout/failure/000';
        }
        else {
          $url = env('CITY_PASS_WEB') . $this->lang . '/checkout/complete/' . $s . '/' . $parameter->orderNo;
        }

        return redirect($url);
      }
    }
}
