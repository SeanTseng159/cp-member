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

use Ksd\Payment\Services\UpDateOrderStatusService;

use App\Traits\CartHelper;

use App\Core\Logger;
use Exception;
use App\Exceptions\CustomException;
use App;
use App\Services\MemberService;
use Ksd\Payment\Services\BlueNewPayService;
class BlueNewPayController extends RestLaravelController
{
    use CartHelper;


    protected $orderService;
    protected $menuOrderService;
    protected $blueNewPayService;

    public function __construct(MenuOrderService $menuOrderService,
                                OrderService $orderService,
                               BlueNewPayService $blueNewPayService)
    {
        $this->orderService = $orderService;
        $this->menuOrderService = $menuOrderService;
        $this->blueNewPayService=$blueNewPayService;
    }

    //購物車一次買完




    //分兩步驟購物
    public function reserve(Request $request)
    {
        try{
            $orderNumber=$request->input('orderNumber');
            //get order data
            $order = $this->orderService->findByOrderNoWithDetail($orderNumber);
            // count how many
            $itemsCount=collect($order->detail)->count();
            $member=App::make(MemberService::class)->find($order->member_id);
            $email=(!empty($member->email))?$member->email:$member->openId;
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
            $result=$this->blueNewPayService->reserve($mobleParams);
            Logger::alert('===end payment data ====');
            if ($result['code'] === '00000') {
                //修改訂單
                (new UpDateOrderStatusService)->upDateOderByOrderNo($orderNumber,['order_status'=>'10','order_paid_at'=> Carbon::now()]);
                (new UpDateOrderStatusService)->upDateOderDetailByOrderNo($orderNumber,['verified_status'=>'10']);

                // 寄送linepay付款完成通知信
                $order = $this->orderService->findByOrderNo($orderNumber);
                dispatch(new OrderPaymentCompleteMail($order->member_id, 'ct_pass', $order->order_no))->delay(5);

                return $this->success();
            }else{
                //修改訂單1
                (new UpDateOrderStatusService)->updateByOrderNo($orderNumber,['order_status'=>'01','order_paid_at'=> Carbon::now()]);
                return $this->failureCode('E9006');
            }

        }catch(Exception $e){
            Logger::alert('=== bluenewpayController deBug===');
            Logger::alert($e);
            return $this->failureCode('E9000');
        }

    }//end payment


}
