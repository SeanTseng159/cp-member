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

use Ksd\Payment\Services\TaiwanPayService;

use App\Traits\CartHelper;


class TaiwanPayController extends RestLaravelController
{
    protected $tiwanPayService;

    public function __construct(TaiwanPayService $tiwanPayService)
    {
        $this->tiwanPayService = $tiwanPayService;
    }

    public function reserve(Request $request)
    {
        try{
            $orderNumber=$request->input('orderNumber');
            //get order data
            $order = $this->orderService->findByOrderNoWithDetail($orderNumber);

            $AcqBank=env('ACQ_BANK');
            $AuthResURL="";
            $lidm=$orderNumber;
            $purchAmt=$request->input('amt');
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
            $result=$this->tiwanPayService->reserve($mobleParams);
            Logger::alert('===end TaiwanPayController data ====');
            //要送資料去前台轉址
            return $this->success($mobleParams);



        }catch(Exception $e){
            Logger::alert('=== bluenewpayController deBug===');
            Logger::alert($e);
            return $this->failureCode('E9000');
        }

    }//end payment

}
