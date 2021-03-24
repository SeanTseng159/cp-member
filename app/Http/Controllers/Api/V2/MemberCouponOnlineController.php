<?php

namespace App\Http\Controllers\Api\V2;
use Carbon\Carbon;
use App\Traits\MemberHelper;

use App\Services\Ticket\MemberCouponOnlineService;
use App\Result\Ticket\MemberCouponOnlineResult;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\Mediation\Services\CartMoreService;//去CI專案的API會需要用到的Service
use App\Traits\CartHelper;

use App\Cache\Redis;
use App\Services\CartService;
use App\Core\Logger;

use App\Services\Ticket\MemberDiscountService;

use Illuminate\Http\Request;
use stdClass;
use App\Result\Ticket\MemberDiscountResult;
use Illuminate\Support\Facades\Hash;
use App\Services\DiscountCodeService;
use App\Services\Ticket\ProductService;

class MemberCouponOnlineController extends RestLaravelController
{
    use CartHelper;
    use MemberHelper;

    protected $service;
    
    protected $cartMoreService;


    public function __construct(
        MemberCouponOnlineService $service,
        CartMoreService $cartMoreService,
        CartService $cartService
    ) {
        $this->service = $service;
        $this->cartMoreService = $cartMoreService;
        $this->cartService = $cartService;
    }

    /**
     * 原商城折價功能僅有站方發送的DiscountCode可以讓商品在購物車結帳時選擇優惠代碼折價(例如listCanUsed等相關功能)
     * 目前新增商家也能夠發行自己的線上商品折扣碼(此功能請看CITY-PASS-VENDOR內的CounponController)
     * 此function主要讓顧客在購物車結帳時，除了站方的優惠券以外，亦可選擇商家發行著折扣碼
     * 
     * @param Request $request cartNumber
     * @return JsonResponse
     */
    public function listCouponOnlineCanUsed(Request $request)
    {   
        //主要流程 : 拿購物車號碼cartNumber，去查是哪家店的商品，再去找user持有的這個店家開立的優惠券，跟商品進行比較，挑出可用與不可用給前端

        //取出會員資訊
        $memberID = $this->getMemberId();
        
        //取出購物車號碼
        $cartNumber = $request->query('cartNumber');

        //透過購物車號碼，取出該購物車的店車id
        $source_diningCar_id = $this->cartService->getDingingCarIDByCartNumber($cartNumber);
        $source_diningCar_id = $source_diningCar_id['dining_car_id'];//原本回傳為物件，將物件中的dining_car_id拿出來

        //將cartNumber跑api送去CI(TPASS)專案，拿出購物車內的商品
        //note:基本上需要拿購物車資料都是送去CI專案的Cart或CartsAddMoreCarts controller下面的detail_post
        //直接從程式上看會找不到兩專案之間的mapping關係，建議直接看code去猜
        $cartItems = $this->cartMoreService->mine(['cartNumber' => $cartNumber]);

        //拿出列表優惠倦
        $member_coupon_online = $this->service->listCanUsed($memberID);
        //DEBUG
        //return $this->success($member_coupon_online);
        //return $this->success($cartItems);

        //return $this->success($member_coupon_online);

        //判斷這些優惠券，有哪些是符合使用資格(期限內、仍有使用數量、符合優惠券最低消費金額等判斷)，符合才拿出來
        $result = (new MemberCouponOnlineResult)->listCanUsed($member_coupon_online, $cartItems, $memberID, $source_diningCar_id);

        return $this->success($result);
    }


}
