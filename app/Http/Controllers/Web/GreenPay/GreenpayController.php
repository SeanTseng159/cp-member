<?php
/**
 * User: Lee
 * Date: 2017/12/20
 * Time: 下午2:20
 */

namespace App\Http\Controllers\Web\GreenPay;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use App\Services\Ticket\OrderService;

use App\Core\Logger;


use App\Traits\MemberHelper;
use Hashids\Hashids;

class GreenpayController extends Controller
{
    use MemberHelper;

    protected $orderService;
    protected $menuOrderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;

    }

    /**
     * 登入
     * @param Illuminate\Http\Request $request
     */
    public function payment(Request $request)
    {
        
        $hash=$request->query('data');

        $data=(new Hashids('Citypass', 12))->decode($hash);;
        $orderNo =$data[0]; 
        $amount = $data[1];
        $source = $request->query('source');
        $platform = $request->query('platform');

        //web or app
        if($platform=='web'){
            $sucessUrl=env('CITY_PASS_WEB').'checkout/complete/c/'.$orderNo;
            $faileUrl=env('CITY_PASS_WEB').'checkout/complete/c/'.$orderNo;
        }else{
            $sucessUrl='app://order?id=' . $orderNo . '&source=ct_pass&result=true&msg=success';
            $faileUrl='app://order?id=' . $orderNo . '&source=ct_pass'.'&result=false&msg=' . '失敗';
        }

        $callbackUrl=env('MIDDLEWARE_URL').'api/v1/checkoutPay/feedback';
      
        $paymentUrl=env('PAYMENT_URL_PATH').'greenecpay/confirm';
        //相關參數
        $submitParameter=[
            'paymentUrl'=>$paymentUrl,
            'orderNo'=>$orderNo,
            'amount' =>$amount,
            'source' =>$source,
            'platform'=>$platform,
            'sucessUrl'=>$sucessUrl,
            'faileUrl'=>$faileUrl,
            'callbackUrl'=>$callbackUrl
        ];
        
        
        return  view('greenpay.index', $submitParameter);
    }


}
