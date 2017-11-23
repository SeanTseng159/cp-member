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
use Carbon;
use Log;

class PayController extends RestLaravelController
{
    protected $service;
    protected $memberService;
    protected $orderService;

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
      $order = $this->orderService->findOneByIpassPay($parameter);
      if (!$order) return $this->failure('E0101', '訂單不存在');
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

    public function successCallback(Request $request)
    {
      Log::debug('=== ipass pay success callback ===');
      Log::debug(print_r($request->all(), true));

      $lang = 'zh_TW';

      $parameter = new CallbackParameter;
      $parameter->laravelRequest($request);

      $url = (env('APP_ENV') === 'production') ? 'http://172.104.83.229/' : 'http://localhost:3000/';
      $url .= $lang;

      if ($parameter->platform === 'app') {
        $url = 'app://order?id=' . $parameter->orderId . '&source=' . $parameter->source . '&result=true&msg=success';
      }
      else {
        $s = ($parameter->source === 'ct_pass') ? 'c' : 'm';
        $url .= '/checkout/complete/' . $s . '/' . $parameter->orderId;
      }

      return redirect($url);
    }

    public function failureCallback(Request $request)
    {
      Log::debug('=== ipass pay failure callback ===');
      Log::debug(print_r($request->all(), true));

      $lang = 'zh_TW';

      $parameter = new CallbackParameter;
      $parameter->laravelRequest($request);

      $url = (env('APP_ENV') === 'production') ? 'http://172.104.83.229/' : 'http://localhost:3000/';
      $url .= $lang;

      if ($parameter->platform === 'app') {
        $url = 'app://order?id=' . $parameter->orderId . '&source=' . $parameter->source . '&result=false&msg=failure';
      }
      else {
        $s = ($parameter->source === 'ct_pass') ? 'c' : 'm';
        $url .= '/checkout/complete/' . $s . '/' . $parameter->orderId;
      }

      return redirect($url);
    }
}
