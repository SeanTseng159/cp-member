<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\Payment\Services\LinePayService;
use Ksd\Payment\Parameter\LinePayParameter;
use App\Services\Ticket\OrderService;
use App\Jobs\Mail\OrderPaymentCompleteMail;

class LinePayController extends RestLaravelController
{
    protected $lang;
    protected $service;
    protected $orderService;

    public function __construct(LinePayService $service, OrderService $orderService)
    {
        $this->service = $service;
        $this->orderService = $orderService;

        $this->lang = env('APP_LANG');
    }

    /**
     * linepay 付款完成 callback, 更新訂單
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmCallback(Request $request)
    {
        $parameters = (new LinePayParameter)->feedback($request);

        if ($parameters['code'] === '00000') {
            $result = $this->service->feedback($parameters['record']);

            // 寄送linepay付款完成通知信
            if ($result['code'] === 201) {
                \Log::debug('=== 寄送linepay付款完成通知信 ===');
                \Log::debug($parameters['record']['orderNo']);
                $order = $this->orderService->findByOrderNo($parameters['record']['orderNo']);
                \Log::debug(print_r($order, true));
                dispatch(new OrderPaymentCompleteMail($order->member_id, 'ct_pass', $order->order_no))->delay(5);
            }

            return ($result['code'] === 201) ? $this->successRedirect($parameters) : $this->failureRedirect($parameters);
        }
        else {
            // Error 跳轉頁
            $webSite = env('CITY_PASS_WEB') . $this->lang . '/checkout/failure/000';
            return redirect($webSite);
        }
    }

    /**
     * linepay 付款完成 callback, 更新訂單
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmCallbackFailure(Request $request)
    {
        $parameters['record']['orderNo'] = $request->input('orderNo');
        $parameters['device'] = $request->input('device');

        return $this->failureRedirect($parameters);
    }

    private function successRedirect($parameters)
    {
        $orderNo = $parameters['record']['orderNo'];

        if ($parameters['device'] === 'ios' || $parameters['device'] === 'android') {
            $url = sprintf('app://order?id=%s&source=ct_pass&result=true&msg=success', $orderNo);

            return sprintf('<script>location.href="%s";</script>', $url);
        }
        else {
            $webSite = env('CITY_PASS_WEB') . $this->lang;
            $url = sprintf('%s/checkout/complete/c/%s', $webSite, $orderNo);

            return redirect($url);
        }
    }

    private function failureRedirect($parameters)
    {
        $orderNo = $parameters['record']['orderNo'];

        if ($parameters['device'] === 'ios' || $parameters['device'] === 'android') {
            $url = sprintf('app://order?id=%s&source=ct_pass&result=failure&msg=failure', $orderNo);

            return sprintf('<script>location.href="%s";</script>', $url);
        }
        else {
            $webSite = env('CITY_PASS_WEB') . $this->lang;
            $url = sprintf('%s/checkout/complete/c/%s', $webSite, $orderNo);

            return redirect($url);
        }
    }
}
