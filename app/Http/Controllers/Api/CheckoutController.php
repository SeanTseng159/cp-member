<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\Mediation\Parameter\Checkout\ConfirmParameter;
use Ksd\Mediation\Parameter\Checkout\ShipmentParameter;
use Ksd\Mediation\Parameter\Checkout\CreditCardParameterm;

use Ksd\Mediation\Services\CheckoutService;
use App\Services\Card3dLogService as LogService;
use Log;

class CheckoutController extends RestLaravelController
{
    protected $service;

    public function __construct(CheckoutService $service)
    {
        $this->service = $service;
    }

    /**
     * 取得結帳資訊
     * @param $source
     * @return \Illuminate\Http\JsonResponse
     */
    public function info($source)
    {
        return $this->success($this->service->info($source));
    }

    /**
     * 設定物流方式
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function shipment(Request $request)
    {
        $parameters = new ShipmentParameter();
        $parameters->laravelRequest($request);
        $this->service->shipment($parameters);
        return $this->success();
    }

    /**
     * 確定結帳
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirm(Request $request)
    {
        $parameters = new ConfirmParameter();
        $parameters->laravelRequest($request);
        $result = $this->service->confirm($parameters);
        return $this->success($result);
    }

    /**
     * 3D驗證
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify3d(Request $request)
    {
        $data = $request->only([
            'source',
            'paymentId',
            'cardNumber',
            'expYear',
            'expMonth',
            'code',
            'totalAmount',
            'orderNo',
            'platform'
        ]);

        $request->session()->put('ccData', $data);

        $data['RetUrl'] = url('api/checkout/verifyResult');

        return view('checkout.verify3d', $data);
    }

    /**
     * 取得3D驗證回傳資料
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyResult(Request $request)
    {
        $lang = 'zh_TW';

        $requestData = $request->only([
            'ErrorCode',
            'ErrorMessage',
            'ECI',
            'CAVV',
            'XID'
        ]);

        // 從session取信用卡資料
        $ccData = $request->session()->pull('ccData', 'default');

        $orderNo = $ccData['orderNo'];
        $platform = $ccData['platform'];
        $source = $ccData['source'];

        // 寫入資料庫
        $requestData['platform'] = ($platform) ?: 'web';
        $requestData['XID'] = $orderNo;
        $requestData['totalAmount'] = $ccData['totalAmount'];
        $requestData['source'] = $source;
        $log = new LogService;
        $result = $log->create($requestData);

        $url = (env('APP_ENV') === 'production') ? 'http://172.104.83.229/' : 'http://localhost:3000/';
        $url .= $lang;

        // 失敗
        /*if (!in_array($data['ECI'], ['5', '2', '6', '1'])) {
            if ($platform === 'app') return redirect('app://order?id=' . $orderNo . '&source=' . $source . '&result=false&msg=' . $data['ErrorMessage']);
        }*/

        Log::info('3D驗證完成');

        // 金流實作
        $parameters = new CreditCardParameterm();
        $parameters->mergeRequest($requestData, $ccData);
        $result = $this->service->creditCard($parameters);

        Log::info('信用卡完成');

        if ($platform === 'app') {
            $url = 'app://order?id=' . $orderNo . '&source=' . $source;

            $url .= ($result) ? '&result=true&msg=success' : '&result=false&msg=' . $requestData['ErrorMessage'];
        }
        else {
            $url .= '/checkout/complete/' . $orderNo . '/' . $source;
        }

        return redirect($url);
    }


    /**
     * 信用卡送金流
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function creditCard(Request $request)
    {
        $parameters = new CreditCardParameterm();
        $parameters->laravelRequest($request);
        $result = $this->service->creditCard($parameters);
        return $this->success($result);
    }
}
