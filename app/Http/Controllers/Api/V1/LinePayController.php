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

class LinePayController extends RestLaravelController
{
    protected $lang;
    protected $service;

    public function __construct(LinePayService $service)
    {
        $this->service = $service;

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

            return ($result['code'] === 201) ? $this->successRedirect($parameters) : $this->failureRedirect($parameters);
        }
        else {
            // Error
            $webSite = env('CITY_PASS_WEB') . $this->lang;
            return redirect($webSite);
        }
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
