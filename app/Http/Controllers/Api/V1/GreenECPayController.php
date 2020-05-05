<?php
/**
 * User: lee
 * Date: 2018/11/29
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;


use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;



use App\Services\Ticket\OrderService;






use App\Core\Logger;


use App\Traits\MemberHelper;
use Hashids\Hashids;
class GreenECPayController extends RestLaravelController
{
    use MemberHelper;

    protected $orderService;
    protected $menuOrderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;

    }

    public function reserve(Request $request)
    {
        try{
            Logger::info('greenECPay reserve');
            $memberID = $this->getMemberId();
            $orderNo=$request->input('orderNo');
            $data=$this->orderService->findCanShowByOrderNo($memberID,$orderNo);
            $hash=(new Hashids('Citypass', 12))->encode([$orderNo,$data->order_amount]);
            $platform=$request->header('platform');
            $url=env('MIDDLEWARE_URL');
            $url.='greenecpay/payment?data='.$hash.'&source=Citypass&platform='.$platform;
            
            return $this->success(['url'=> $url ]);
        }catch (Exception $e) {
            Logger::debug('=== greenECPAY  reserve deBug===');
            Logger::debug($e);
            return $this->failureCode('E0001');
        }
    }//end reserve


}
