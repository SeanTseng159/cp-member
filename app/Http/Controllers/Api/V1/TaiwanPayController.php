<?php
/**
 * User: lee
 * Date: 2018/11/29
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

// use App\Services\MenuOrderService;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use App\Services\Ticket\OrderService;
use Ksd\Payment\Services\TaiwanPayService;
use App\Core\Logger;
use App\Traits\CartHelper;
use Ksd\Payment\Services\UpDateOrderStatusService;
use App\Jobs\Mail\OrderPaymentCompleteMail;
class TaiwanPayController extends RestLaravelController
{
    protected $taiwanPayService;
    protected $orderService;
    protected $upDateOrderStatusService;
    public function __construct(TaiwanPayService $taiwanPayService,OrderService $orderService,UpDateOrderStatusService $upDateOrderStatusService)
    {
        $this->taiwanPayService = $taiwanPayService;
        $this->orderService=$orderService;
        $this->upDateOrderStatusService=$upDateOrderStatusService;
    }

    public function comfirm(Request $request)
    {
        try{
            $orderNumber=$request->input('orderNumber');
            //get order data
            $order = $this->orderService->findByOrderNoWithDetail($orderNumber);
            $AcqBank=env('ACQ_BANK');
            $AuthResURL=env('CITY_PASS_WEB').'zh-TW/processing';
            $lidm=$orderNumber;
            $purchAmt=$order->order_amount;
            $MerchantID=env('MERCHANT_ID');
            $TerminalID=env('TERMINAL_ID');
            $VerificationParameters=env('VERIFICATION_PARAMETERS');
            $reqToken=hash('sha256',"{$AcqBank}&{$AuthResURL}&{$lidm}&{$MerchantID}&{$purchAmt}&{$TerminalID}&{$VerificationParameters}");
            // 請參考taiwanpay文件要送出的資料
            $mobleParams=["AcqBank" => $AcqBank,
                          "AuthResURL" => $AuthResURL,
                          "lidm" => $lidm,
                          "MerchantID" => $MerchantID ,
                          "purchAmt" => $purchAmt,
                          "reqToken" => $reqToken,
                          "TerminalID" => $TerminalID
                       ];
            Logger::alert('===for TaiwanPayController data ====');
            //要送資料送去paymentgetway 紀錄log
            $result=$this->taiwanPayService->saveTransacctions($mobleParams);
            Logger::alert('===end TaiwanPayController data ====');
            //要送資料去前台轉址
            return $this->success($mobleParams);
        }catch(Exception $e){
            Logger::alert('=== bluenewpayController  comfirm deBug===');
            Logger::alert($e);
            return $this->failureCode('E9000');
        }

    }//end comfirm


    public function callback(Request $request)
    {
        try{
            Logger::alert('=== bluenewpayController  callback get data back===');

             // 請參考taiwanpay文件要送出的資料
             $mobleParams=["authAmt" => $request->input('authAmt'),
             "authRespTime" => $request->input('authRespTime'),
             "lidm" => $request->input('lidm'),
             "MerchantID" => $request->input('MerchantID'),
             "respCode" => $request->input('respCode'),
             "respToken" => $request->input('respToken'),
             "Srrn" => $request->input('Srrn')
            ];
          //要送資料送去paymentgetway 紀錄log
            $this->taiwanPayService->saveTransacctionsReturn($mobleParams);
            //確認回傳資料
            if($request->input('respCode')==='0000'){
                //修改訂單
                $this->upDateOrderStatusService->upDateOderByOrderNo($request->input('lidm'),['order_status'=>'10','order_paid_at'=> Carbon::now()]);
                $this->upDateOrderStatusService->upDateOderDetailByOrderNo($request->input('lidm'),['verified_status'=>'10']);
                // 寄送linepay付款完成通知信
                $order = $this->orderService->findByOrderNo($request->input('lidm'));
                dispatch(new OrderPaymentCompleteMail($order->member_id, 'ct_pass', $order->order_no))->delay(5);
                return $this->success();
            }else{

            }

        } catch(Exception $e){
            Logger::alert('=== bluenewpayController  callback deBug===');
            Logger::alert($e);
            return $this->failureCode('E9000');
        }
    }//end callback

}
