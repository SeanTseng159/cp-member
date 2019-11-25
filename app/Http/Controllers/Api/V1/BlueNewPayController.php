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

    public function confirm(Request $request)
    {
        try{

            $mobleParams=new \stdClass;
            $orderNumber=$request->input('orderNumber');
            //get order data
            $order = $this->orderService->findByOrderNoWithDetail($orderNumber);
            // count how many
            $itemsCount=collect($order->detail)->count();
            $member=App::make(MemberService::class)->find($order->member_id);
            $mobleParams->PayerEmail=(!empty($member->email))?$member->email:$member->openId;


            $mobleParams->MerchantOrderNo=$orderNumber;
            $mobleParams->Amt=$order->order_amount;
            $mobleParams->ProdDesc="CityPass 商品 - 共 {$itemsCount} 項";
            $mobleParams->PayerEmail=(!empty($member->email))?$member->email:$member->openId;
            $mobleParams->device=$request->input('device');
            $mobleParams->payMethod=$request->input('payMethod');
            $mobleParams->token=$request->input('token');
            $result=$this->blueNewPayService->confirm($mobleParams);

            if ($result['code'] === '00000') {

                $this->orderService->updateByOrderNo($orderNumber,['order_status'=>10]);
                // 寄送linepay付款完成通知信
                $order = $this->orderService->findByOrderNo($orderNumber);
                dispatch(new OrderPaymentCompleteMail($order->member_id, 'ct_pass', $order->order_no))->delay(5);

                return $this->success();
            }else{
                return $this->failureCode('E9006');
            }

        }catch(Exception $e){
            return $this->failureCode('E9000');
        }

    }//end payment


}
