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
use App\Services\MemberService;
use Ksd\Mediation\Services\OrderService;
use Ksd\IPassPay\Parameter\PayParameter;
use Ksd\IPassPay\Parameter\CallbackParameter;
use Ksd\IPassPay\Parameter\OrderParameter;
use Carbon;
use Log;

class PayController extends RestLaravelController
{
    protected $service;
    protected $memberService;
    protected $orderService;

    const MAGENTO = 'magento';
    const CITYPASS = 'ct_pass';

    public function __construct(PayService $service, MemberService $memberService, OrderService $orderService)
    {
        $this->service = $service;
        $this->memberService = $memberService;
        $this->orderService = $orderService;
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

      // 檢查會員
      $result = $this->memberService->checkToken($parameter->token, $parameter->platform);
      if (!$result) return $this->failure('E0021','會員驗證失效');

      // EC平台請求支付Token (步驟一)
      try {
        $order = $this->orderService->findOneByIpassPay($parameter);
        // if (!$order) return $this->failure('E0101', '訂單不存在');
        $bindPayParameter = $parameter->bindPayReq($order);
        $result = $this->service->bindPayReq($bindPayParameter);

        Log::debug('=== ipass pay step 1 ===');
        Log::debug(print_r($result['data'], true));
        if (!$result['status']) return $this->failure('E0000', $result['data']->rtnMsg);

        // 取得Token到ipass pay 付款介面 (步驟二)
        $bindPayParameter = $parameter->bindPayToken($result['data']);

        Log::debug('=== ipass pay step 2 ===');
        Log::debug(print_r($bindPayParameter, true));
        return view('ipass::pay', ['parameter' => $bindPayParameter]);
      }
      catch (Exception $e) {
        Log::debug('=== ipass pay error ===');
        Log::debug(print_r($e, true));
      }
    }

    public function successCallback(Request $request)
    {
      Log::debug('=== ipass pay success callback ===');
      Log::debug(print_r($request->all(), true));

      $callbackParameter = new CallbackParameter;
      $callbackParameter->laravelRequest($request);

      // 跟ipass確認付款
      $payParameter = new PayParameter;
      $bindPayStatusParameter = $payParameter->bindPayStatus($callbackParameter->callback);
      $payStatusResult = $this->service->bindPayStatus($bindPayStatusParameter);

      Log::debug('=== ipass pay check pay success ===');
      Log::debug(print_r($payStatusResult, true));

      // 失敗導回前端
      if (!$payStatusResult['status']) return $this->failureRedirect($callbackParameter);

      // 送後端訂單更新
      $orderParameter = new OrderParameter;
      $updateOrderParameter = $orderParameter->updateParameter($payStatusResult['data'], $callbackParameter);
      $updateResult = $this->orderService->update($callbackParameter->token, $updateOrderParameter);

      Log::debug('=== update order ===');
      Log::debug(print_r($updateResult, true));

      $result = false;
      if ($callbackParameter->source === SELF::MAGENTO) {

      }
      elseif ($callbackParameter->source === SELF::CITYPASS) {
        $result = ($updateResult && $updateResult['statusCode'] === '201');
      }

      // 失敗導回前端
      if (!$result) return $this->failureRedirect($callbackParameter);

      // 成功導回前端
      return $this->successRedirect($callbackParameter);
    }

    public function failureCallback(Request $request)
    {
      Log::debug('=== ipass pay failure callback ===');
      Log::debug(print_r($request->all(), true));

      $lang = 'zh_TW';

      $parameter = new CallbackParameter;
      $parameter->laravelRequest($request);

      return $this->failureRedirect($parameter);
    }

    private function successRedirect($parameter)
    {
      $lang = 'zh_TW';

      $url = (env('APP_ENV') === 'production') ? env('CITY_PASS_WEB') : 'http://localhost:3000/';
      $url .= $lang;

      if ($parameter->platform === 'app') {
        $url = 'app://order?id=' . $parameter->orderNo . '&source=' . $parameter->source . '&result=true&msg=success';

        return '<script>location.href="' . $url . '";</script>';
      }
      else {
        $s = ($parameter->source === SELF::CITYPASS) ? 'c' : 'm';
        $url .= '/checkout/complete/' . $s . '/' . $parameter->orderNo;

        return redirect($url);
      }
    }

    private function failureRedirect($parameter)
    {
      $lang = 'zh_TW';

      $url = (env('APP_ENV') === 'production') ? env('CITY_PASS_WEB') : 'http://localhost:3000/';
      $url .= $lang;

      if ($parameter->platform === 'app') {
        $url = 'app://order?id=' . $parameter->orderNo . '&source=' . $parameter->source . '&result=false&msg=failure';

        return '<script>location.href="' . $url . '";</script>';
      }
      else {
        $s = ($parameter->source === SELF::CITYPASS) ? 'c' : 'm';
        $url .= '/checkout/complete/' . $s . '/' . $parameter->orderNo;

        return redirect($url);
      }
    }
}
