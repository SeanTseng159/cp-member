<?php

namespace App\Http\Controllers\Api\V2;

use App\Cache\Redis;
use Ksd\Mediation\Services\CartMoreService;
use App\Core\Logger;

use Carbon\Carbon;

use App\Services\Ticket\MemberDiscountService;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use stdClass;
use App\Traits\MemberHelper;
use App\Traits\CartHelper;
use App\Result\Ticket\MemberDiscountResult;
class MemberDiscountController extends RestLaravelController
{
    use CartHelper;
    use MemberHelper;

    protected $cartService;
    protected $service;

    public function __construct(CartMoreService $cartService,
                                MemberDiscountService  $service
                                )
    {
        $this->cartService = $cartService;
        $this->service=$service;
  
    }

    //優惠卷列表
    public function listCanUsed(Request $request)
    {
        try{


            //取出會員資訊
            $memberID = $this->getMemberId();
            //取出購物車號碼
            $cartNumber=$request->query('cartNumber');            
            //跑api拿出購物車內的商品送去給CI專案吧
            $cartItems=$this->cartService->mine(['cartNumber'=>$cartNumber]); 
            //拿出列表優惠倦
            $member_discounts=$this->service->listCanUsed($memberID);
            //判斷得程式
            $result=(new MemberDiscountResult)->listCanUsed($member_discounts,$cartItems,$memberID);
            
            return $this->success($result);
            

            //拿出 dicount_codes
        }catch (\Exception $e) {
            // Logger::error('v2/MemberDiscountController/list',$e->getMessage());
            return $this->failure('E0001', $e->getMessage());
        }//try


        

    }//end list

    
}
