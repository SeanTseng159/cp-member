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
use App\Services\DiscountCodeService;
use App\Services\Ticket\CouponService;
use App\Services\Ticket\MemberDiscountService;
use Ksd\Mediation\CityPass\Order;
use App\Services\Card3dLogService as LogService;
use App\Services\Ticket\OrderService as TicketOrderService;
use App\Jobs\Mail\OrderPaymentCompleteMail;
use App\Jobs\SMS\OrderPaymentComplete as OrderPaymentCompleteSMS;
use Log;

use App\Result\Ticket\OrderResult;

use App\Services\MemberService;
use Ksd\Payment\Services\LinePayService;
use Ksd\Payment\Services\BlueNewPayService;
use App\Traits\StringHelper;
use App\Traits\CartHelper;
use App\Traits\MemberHelper;

class CheckoutController extends RestLaravelController
{
    use StringHelper;
    use CartHelper;
    use MemberHelper;

    protected $lang;
    protected $service;
    protected $cartService;
    protected $discountCodeService;
    protected $couponService;
    protected $memberdiscountCodeservice;

    public function __construct(
        CheckoutService $service,
        CartService $cartService,
        DiscountCodeService $discountCodeService,
        MemberDiscountService $memberdiscountCodeservice,
        CouponService $couponService)
    {
        $this->service = $service;
        $this->cartService = $cartService;
        $this->discountCodeService = $discountCodeService;
        $this->couponService = $couponService;
        $this->memberdiscountCodeservice = $memberdiscountCodeservice;
        $this->lang = env('APP_LANG');
    }

    /**
     * 取得結帳資訊
     * @param $source
     * @return \Illuminate\Http\JsonResponse
     */
    public function info($source)
    {
        $isMagento = false;
        if($source == 'magento' or $source == 'ct_pass_physical') $isMagento = true;
        $source ='ct_pass';

        return $this->success($this->getCheckoutInfo($isMagento, $source, 'array'));
        // return $this->success($this->service->info($source));
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
        return ($result === '00000') ? $this->success() : $this->failureCode($result);
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

        //先拿code去判斷這個code是屬於站方優惠碼(discountCode)還是商家線上優惠券(coupons)，因為不管是哪個單位的都會從前端的"code"帶過來
        if (!empty($parameters->code)) {
            if (!$this->discountCodeService->DiscountCodeExist($parameters->code)) {//先判斷是不是站方的，如果不是就判斷是不是商家的
                if ($this->couponService->checkEnableAndExistByCode($parameters->code)) {
                    //此部分邏輯是，CI專案會判斷member送過去的code欄位跟online_code欄位
                    //若code有值代表有使用站方優惠，online_code有值代表使用商家優惠，都沒值代表沒使用，此兩欄位不會同時有值
                    //故若判斷優惠是商家優惠，則把code的值轉移到online_code，讓CI知道是商家優惠
                    $parameters->online_code = $parameters->code;
                    $parameters->code = "";
                }
            }
        }

        $date = date('Y-m-d H:i:s');
        $memberID = $this->getMemberId();
        $isInvalidDiscountCode = $this->discountCodeService->isInvalidDiscountCode($parameters->code);
        $usedDiscountCode = $this->memberdiscountCodeservice->used($memberID);

        if ($isInvalidDiscountCode != null) {
            foreach ($usedDiscountCode as $key => $item) {              
                if ($item->discountCode->discount_code_value == $parameters->code) {
                    if ($item->discountCode->discount_code_status == 1 && $item->discountCode->discount_code_starttime <= $date && $item->discountCode->discount_code_endtime > $date) {
                        if ($item->discountCode->discount_code_limit_count > $item->discountCode->discount_code_used_count) {
                            if ($isInvalidDiscountCode->discount_code_member_use_count != 0) {
                                return $this->failureCode('E0073');
                            }   
                        } else {
                            return $this->failureCode('E0073');
                        }
                    } else {
                        return $this->failureCode('E0073');
                    }
                }
            }
        }

        $result = $this->service->confirm($parameters);

        if ($parameters->payment->type === 'linepay' && $result['code'] === 201) {
            $linePayService = app()->build(LinePayService::class);
            $reserveResult = $linePayService->reserve($result['data'], $parameters);

            if ($reserveResult['code'] === '00000') {
                $result['data']['paymentUrl'] = $reserveResult['data']['paymentUrl'];
            }
            else {
                return $this->failure($reserveResult['code'], $reserveResult['message']);
            }
        }

        if ($result['code'] === '00000' || $result['code'] === 201) {
            return $this->success($result['data']);
        }
        else if ($result['code'] === 402) {
            return $this->failureCode('E9007');
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

        $url = env('CITY_PASS_WEB');
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

    /**
     * LinePay comfirm
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function linepayUpdateOrder(Request $request)
    {
        $orderId = $request->input('orderId');

        if (!$orderId) return $this->failureCode('E0001');

        $parameters['orderId'] = $orderId;

        $linePayService = app()->build(LinePayService::class);
        $confirmResult = $linePayService->confirm($parameters);

        if ($confirmResult['code'] === '00000') {

            $record = [
                'orderNo' => $confirmResult['data']['orderId'],
                'amount'   => $confirmResult['data']['amount'],
                'status'   => 1,
                'transactionId' => $confirmResult['data']['transactionId']
            ];

            // 更新訂單
            $result = $this->service->feedback($record);

            // 寄送linepay付款完成通知信
            if ($request->memberId == 0) {
                // 訪客
                dispatch(new OrderPaymentCompleteSMS($orderId))->delay(10);
            }
            else {
                // 一般會員
                dispatch(new OrderPaymentCompleteMail($request->memberId, 'ct_pass', $orderId))->delay(10);
            }
        }


        // 撈取訂單 (new)
        $ticketOrderService = app()->build(TicketOrderService::class);

        if ($request->memberId == 0) {
            // 訪客
            $order = $ticketOrderService->findByOrderNoWithGuestOrder($orderId);
            $newOrder[] = (new OrderResult)->get($order, true, $order->guestOrder->name);
        }
        else {
            // 一般會員
            $order = $ticketOrderService->findByOrderNo($orderId);
            $newOrder[] = (new OrderResult)->get($order, true);
        }

        return $this->success($newOrder);
    }

    /**
     * 更新訂單資訊
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function feedbackPay(Request $request){

        $parameters= [
            'orderNo' => $request->input('orderNo'),
            'amount'   => $request->input('amount'),
            'status'   => $request->input('status')
        ];
        $data=$this->service->feedbackPay($parameters);
        return $this->success($data);
    }

}
