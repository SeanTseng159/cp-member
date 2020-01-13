<?php
/**
 * User: jerry
 * Date: 2020/01/06
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

// use App\Services\MenuOrderService;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use App\Services\Ticket\OrderService;

use Ksd\Payment\Services\YushanPayService;

use App\Core\Logger;
use App\Jobs\Mail\OrderPaymentCompleteMail;
use Ksd\Mediation\Services\CheckoutService;
use Agent;
use Carbon\Carbon;
use Exception;
use Hashids\Hashids;
class YushanPayController extends RestLaravelController
{
    protected $yushanPayService;
    protected $orderService;
    protected $checkoutService;
    public function __construct(YushanPayService $yushanPayService,OrderService $orderService,CheckoutService $checkoutService)
    {
        $this->yushanPayService = $yushanPayService;
        $this->orderService=$orderService;
        $this->checkoutService=$checkoutService;
    }

    public function comfirm(Request $request)
    {
        Logger::alert('YushanPay start');
        try{
            //拿到訂單資訊
            $orderNumber=$request->input('orderNumber');
            //get order data
            $order = $this->orderService->findByOrderNoWithDetail($orderNumber);

            $HASHKey=env('HASHKey');
            $seller_id=env('SELLER_ID');
            $validate_method='sign';
            $version='1.0';
            //使用手機要給一個網址 $murl;
            if (Agent::isMobile() || \Request::header('platform') == 'app') {
                $device='mobile';
                $murl=env('CITY_PASS_WEB');
            }elseif (Agent::isTablet()) {
                $device='mobile';
                $murl=env('CITY_PASS_WEB');
            }else {
                $device='pc';
                $murl=env('CITY_PASS_WEB');
            }
            //$pno=$orderNumber;
            $pno=$request->input('orderNumber'); 
            $ntd=$order->order_amount;
            $return_url=env('CITY_PASS_WEB').'processing';
            $ttime=Carbon::now()->format('YmdHis');
            $pname=$orderNumber;
            $data=collect($order->detail)->groupBy('prod_spec_price_id');            
            //將資料整理一下
            $result=['seller_id'=>$seller_id,
                    'device'=>$device,
                    'murl'=>$murl,
                    'pno'=>$pno,
                    'ntd'=>$ntd,
                    'return_url'=>$return_url,
                    'ttime'=>$ttime,
                    'count'=>'1',
                    'pname'=>$pname,
                    'validate_method'=>$validate_method,
                    'pid0' =>'1',
                    'qty0' =>'1'
                    // 'version'=>$version
                ];
            //計數器
            // $countForeach=0;
            // foreach ($data as $key=> $value){
            //     $result['pid'.$countForeach]=$key;
            //     $result['qty'.$countForeach]=$value->count();
            //     $countForeach++;
            // }
            //排序後要處理pcode
            ksort($result);
            
            $wordPcode='';
            foreach ($result as $value){
                $wordPcode=$wordPcode.$value;
            }
            $pcode=SHA1($wordPcode.$HASHKey);
            $result['pcode']=$pcode;
            $url=env('YushanPay_url');
            Logger::alert('===end YushanPay data ====');
            //要送資料去前台轉址
            return $this->success($url.http_build_query($result));
        }catch(Exception $e){
            Logger::debug('=== YushanPay  comfirm deBug===');
            Logger::debug($e);
            return $this->failureCode('E9000');
        }

    }//end comfirm


    public function callback(Request $request)
    {
        try{
            Logger::alert('=== YushanPay  callback get data back===');
            Logger::alert($request->input('pno'));
            // 去玉山確認訂單是否付款
            // 整理參數
            $parameters=['action'=>'ByOrder',
                        'seller_id'=>env('SELLER_ID'),
                        'pno'=>$request->input('pno')];
            $res=$this->yushanPayService->checkYushanOrder($parameters);
            if((string)$res->ResultCode=='OK'){
                $parameters= [
                    'orderNo' => (string)$res->Orders->Order->pno,
                    'amount'   => (string)$res->Orders->Order->ntd,
                    'order_no'   => (string)$res->Orders->Order->order_no,
                    'channel_order_no'   => (string)$res->Orders->Order->channel_order_no,
                    'alipayno' => (string)$res->Orders->Order->alipayno,
                    'ttime' => (string)$res->Orders->Order->ttime,
                    'status'   => 1
                ];
                //修改訂單,送去TPSS專案裡面修改訂單資訊
                $result = $this->checkoutService->feedbackPay($parameters);
                
                $order = $this->orderService->findByOrderNo((string)$res->Orders->Order->pno);
                if(empty($order)){
                    throw new Exception('沒有訂單');
                }
                //要送資料送去paymentgetway 紀錄log
                $result=$this->yushanPayService->saveTransacctions($parameters);
                // 寄送pay付款完成通知信
                dispatch(new OrderPaymentCompleteMail($order->member_id, 'ct_pass', $order->order_no))->delay(5);
                //成功回傳空值
                return $this->success();
            }else{
                Logger::alert('=== YushanPay  callback fail pay orderid is '.$request->input('pno'));
                Logger::alert('=== YushanPay  callback fail pay message'.(string)$res->Message);
                $parameters= [
                    'orderNo' => $request->input('pno'),
                    'amount'   => $request->input('ntd'),
                    'status'   => 0
                ];
                //要送資料送去paymentgetway 紀錄log
                $result=$this->yushanPayService->saveTransacctions($parameters);
                //修改訂單1
                $this->checkoutService->feedbackPay($parameters);
                return $this->responseFormat(null, 'E9006',(string)$res->Message, 200);
            }//end if status ==OK


        } catch(Exception $e){
            Logger::alert('=== YushanPay  callback deBug===');
            Logger::alert($e);
            return $this->failureCode('E9000');
        }
    }//end callback

}
