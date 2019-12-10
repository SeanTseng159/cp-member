<?php
/**
 * User: lee
 * Date: 2018/11/29
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use App\Services\MenuOrderService;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;


use App\Jobs\Mail\OrderPaymentCompleteMail;
use App\Services\CartService;
use App\Services\Ticket\OrderService;
use App\Services\PaymentService;



use App\Traits\CartHelper;

use App\Core\Logger;
use Exception;
use App\Exceptions\CustomException;
use App;
use App\Services\MemberService;
use Ksd\Payment\Services\BlueNewPayService;
use Carbon\Carbon;
use Ksd\Mediation\Services\CheckoutService;
class BlueNewPayController extends RestLaravelController
{
    use CartHelper;


    protected $orderService;
    protected $menuOrderService;
    protected $blueNewPayService;
    protected $checkoutService;
    public function __construct(MenuOrderService $menuOrderService,
                                OrderService $orderService,
                               BlueNewPayService $blueNewPayService,
                               CheckoutService $checkoutService)
    {
        $this->orderService = $orderService;
        $this->menuOrderService = $menuOrderService;
        $this->blueNewPayService=$blueNewPayService;
        $this->checkoutService=$checkoutService;
    }

    //購物車一次買完




    //分兩步驟購物
    public function reserve(Request $request)
    {
        try{
            $orderNumber=$request->input('orderNumber');
            //get order data
            $order = $this->orderService->findByOrderNoWithDetail($orderNumber);
            //檢查是否有沒有單號
            if(empty($order)){
                return $this->failureCode('E9000');
            }
            //檢查是否已經結完帳
            if($order->order_status == 10){
                return $this->failureCode('E9004');
            }
            // count how many
            $itemsCount=collect($order->detail)->count();
            $member=App::make(MemberService::class)->find($order->member_id);
            // email有些放在email 有些放在openID
            $email=(!empty($member->email))?$member->email:$member->openId;
            // 請參考藍新文件要送出的資料
            $mobleParams=["MerchantOrderNo" => $orderNumber,
                          "Amt" => $order->order_amount,
                          "ProdDesc" => "CityPass 商品 - 共 {$itemsCount} 項",
                          "PayerEmail" => $email ,
                          "device" => $request->input('device'),
                          "payMethod" => $request->input('payMethod'),
                          "token" => $request->input('token')
                       ];
            Logger::alert('===for payment data ====');
            Logger::alert($mobleParams['MerchantOrderNo']);
            //要送資料去paymentgetway 付款
            $result=$this->blueNewPayService->reserve($mobleParams);
            Logger::alert('===end payment data ====');
            if ($result['code'] === '00000') {
                $parameters= [
                    'orderNo' => $orderNumber,
                    'amount'   => $order->order_amount,
                    'status'   => 1
                ];
                //修改訂單
                $result = $this->checkoutService->feedbackPay($parameters);
                // 寄送pay付款完成通知信
                $order = $this->orderService->findByOrderNo($orderNumber);
                dispatch(new OrderPaymentCompleteMail($order->member_id, 'ct_pass', $order->order_no))->delay(5);
                //成功回傳空
                return $this->success();
            }else{
                $parameters= [
                    'orderNo' => $orderNumber,
                    'amount'   => $order->order_amount,
                    'status'   => 0
                ];
                //修改訂單1
                $this->checkoutService->feedbackPay($parameters);
                // return $this->failureCode('E9006');
                if(empty($result['message'])){
                    $message='交易失敗，請重新付款';
                }else{
                    $message=$result['message'];
                }
                return $this->responseFormat(null, 'E9006',$message, 200);
            }
        }catch(Exception $e){
            Logger::alert('=== bluenewpayController deBug===');
            Logger::alert($e);
            return $this->failureCode('E9000');
        }

    }//end payment


}
