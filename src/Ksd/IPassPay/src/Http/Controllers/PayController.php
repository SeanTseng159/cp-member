<?php
/**
 * User: Lee
 * Date: 2017/10/27
 * Time: 下午2:20
 */

namespace Ksd\IPassPay\Http\Controllers;

use Illuminate\Http\Request;
use Ksd\IPassPay\Core\Controller\RestLaravelController;
use Ksd\IPassPay\Services\IPassPayService;
use App\Services\MemberService;
use Ksd\Mediation\Parameter\Checkout\ConfirmParameter;
use Carbon;

class PayController extends RestLaravelController
{
    protected $service;
    protected $memberService;

    public function __construct(IPassPayService $service, MemberService $memberService)
    {
        $this->service = $service;
        $this->memberService = $memberService;
    }

    /**
     * ipass pay
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pay(Request $request)
    {
      $token = $request->input('_token');
      $platform = $request->input('_platform');
      $order_id = $request->input('order_id');

      $result = $this->memberService->checkToken($token, $platform);

      // 取得訂單資料後，送ipass pay
      // 撈訂單實做
      $order = new \stdClass();
      $order->client_id = 'ipass1106';
      $order->respond_type = 'json';
      $order->version = '1.0';
      $order->lang_type = 'zh-tw';
      $order->order_id = 'ipass1106';
      $order->order_name = '遠方海洋樂園一日卷';
      $order->amount = '660';
      $order->item_name = '遠方海洋樂園一日卷 X1';
      $order->success_url = url('ipass/callback');
      $order->failure_url = url('ipass/callback');
      $order->timestamp = Carbon\Carbon::now()->timestamp;
      $order->client_pw = 'MjFmNmU0YjMtZWM2Mi00NWRkLTk4NDctMDRlNTlkYmY1ZmJl';
      $order->signature = '';

      foreach ($order as $key => $value) $order->signature .= $value;
      $order->signature = hash('sha256', $order->signature);

      return $this->service->bindPayReq($order);


      //return view('ipass::pay', ['order' => $order]);
    }

    public function callback(Request $request)
    {

    }
}
