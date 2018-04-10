<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Checkout\Shipment;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\Mediation\Parameter\Checkout\ConfirmParameter;
use Ksd\Mediation\Parameter\Checkout\ShipmentParameter;
use Ksd\Mediation\Parameter\Checkout\CreditCardParameter;
use Ksd\Mediation\Parameter\Checkout\TransmitParameter;
use Ksd\Mediation\Parameter\Checkout\PostBackParameter;
use Ksd\Mediation\Parameter\Checkout\ResultParameter;

use Ksd\Mediation\Services\CheckoutService;
use Ksd\Mediation\Services\CartService;
use App\Services\Card3dLogService as LogService;
use App\Jobs\Mail\OrderCreatedMail;
use Log;

class CheckoutController extends RestLaravelController
{
    protected $lang;
    protected $service;
    protected $cartService;

    public function __construct(CheckoutService $service, CartService $cartService)
    {
        $this->service = $service;
        $this->cartService = $cartService;

        $this->lang = env('APP_LANG');
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
    public function shipment(Shipment $request)
    {
        $parameters = new ShipmentParameter();
        $parameters->laravelRequest($request);
        $result = $this->service->shipment($parameters);
        return ($result) ? $this->success() : $this->failure('E9002', '設定物流失敗');
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

        if ($result['code'] === '00000' || $result['code'] === 201) {
            return $this->success($result['data']);
        }
        else {
            return $this->failureCode($result['code']);
        }
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

        Log::info('verify3d資料');
        Log::debug(print_r($data, true));

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
        $requestData = $request->only([
            'ErrorCode',
            'ErrorMessage',
            'ECI',
            'CAVV',
            'XID'
        ]);

        Log::info('verify3dcallback資料');
        Log::debug(print_r($requestData, true));

        // 從session取信用卡資料
        $ccData = $request->session()->pull('ccData', 'default');
        Log::info('session信用卡資料');
        Log::debug(print_r($ccData, true));

        $orderNo = $ccData['orderNo'];
        $platform = $ccData['platform'];
        $source = $ccData['source'];

        // 寫入資料庫
        $requestData['platform'] = ($platform) ?: 'web';
        $requestData['XID'] = $orderNo;
        $requestData['totalAmount'] = $ccData['totalAmount'];
        $requestData['source'] = $source;
        $log = new LogService;
        $log->create($requestData);

        $url = (env('APP_ENV') === 'production') ? env('CITY_PASS_WEB') : 'http://localhost:3000/';
        $url .= $this->lang;

        // 失敗
        /*if (!in_array($data['ECI'], ['5', '2', '6', '1'])) {
            if ($platform === 'app') return redirect('app://order?id=' . $orderNo . '&source=' . $source . '&result=false&msg=' . $data['ErrorMessage']);
        }*/

        Log::info('3D驗證完成');

        // 金流實作
        $parameters = new CreditCardParameter();
        $parameters->mergeRequest($requestData, $ccData);
        $result = $this->service->creditCard($parameters);

        Log::info('信用卡完成');

        if ($platform === 'app') {
            $url = 'app://order?id=' . $orderNo . '&source=' . $source;

            $url .= ($result) ? '&result=true&msg=success' : '&result=false&msg=' . $requestData['ErrorMessage'];

            echo '<script>location.href="' . $url . '";</script>';
            return;

        }
        else {
            $s = ($source === 'ct_pass') ? 'c' : 'm';
            $url .= '/checkout/complete/' . $s . '/' . $orderNo;

            return redirect($url);
        }
    }


    /**
     * 信用卡送金流(藍新)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function creditCard(Request $request)
    {
        $parameters = new CreditCardParameter();
        $parameters->laravelRequest($request);
        $result = $this->service->creditCard($parameters);
        return !empty($result) ? $this->success($result) : $this->failure('E9003', '刷卡失敗');
    }

    /**
     * 信用卡送金流(台新)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transmit(Request $request)
    {
        $parameters = new TransmitParameter();
        $parameters->laravelRequest($request);
        $result = $this->service->transmit($parameters);
        // 清空magento購物車快取
//        if(!empty($result)) $this->cartService->cleanCacheMagento();
        return !empty($result) ? $this->success($result) : $this->failure('E9003', '刷卡失敗');
    }

    /**
     * 接收台新信用卡前台通知程式 post_back_url
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postBack(Request $request)
    {
        $parameters = new PostBackParameter();
        $parameters->laravelRequest($request);
        $url = $this->service->postBack($parameters);

        \Log::debug('=== 台新導向URL ===');
        \Log::debug(print_r($url, true));



        if($url['platform'] === '2') {
            return '<script>location.href="' . $url['urlData'] . '";</script>';
        }else{
            return redirect($url['urlData']);
        }
    }


    /**
     * 接收台新信用卡後台通知程式 result_url
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function result(Request $request)
    {
        $parameters = new ResultParameter();
        $parameters->laravelRequest($request);
        $result = $this->service->result($parameters);
        return $this->success($result);
    }
}
