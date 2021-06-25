<?php

namespace App\Http\Controllers\Api\V2;

use App\Cache\Redis;
use Ksd\Mediation\Services\CartMoreService;
use App\Services\CartService;
use App\Core\Logger;
use App\Models\Coupon;
use App\Models\MemberCoupon;
use App\Result\Ticket\MemberCouponResult;
use Carbon\Carbon;

use App\Services\Ticket\MemberDiscountService;
use App\Services\Ticket\MemberCouponService;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use stdClass;
use App\Traits\MemberHelper;
use App\Traits\CartHelper;
use App\Result\Ticket\MemberDiscountResult;
use Illuminate\Support\Facades\Hash;
use App\Services\DiscountCodeService;
use App\Services\Ticket\CouponService;
use App\Services\Ticket\ProductService;
use App\Services\Ticket\DiningCarService;


class MemberDiscountController extends RestLaravelController
{
    use CartHelper;
    use MemberHelper;

    protected $cartService;
    protected $cartMoreService;
    protected $service;
    protected $discountCodeService;
    protected $couponService;
    protected $productService;
    protected $diningCarService;
    protected $memberCouponService;

    public function __construct(
        CartService $cartService,
        CartMoreService $cartMoreService,
        MemberDiscountService  $service,
        DiscountCodeService $discountCodeService,
        CouponService $couponService, 
        ProductService $productService,
        DiningCarService $diningCarService,
        MemberCouponService $memberCouponService,
        MemberCouponResult $memberCouponResult
    ) {
        $this->cartService = $cartService;
        $this->cartMoreService = $cartMoreService;
        $this->service = $service;
        $this->discountCodeService = $discountCodeService;
        $this->couponService = $couponService;
        $this->productService = $productService;
        $this->diningCarService = $diningCarService;
        $this->memberCouponService = $memberCouponService;
        $this->memberCouponResult = $memberCouponResult;
    }


    /**
     *  //優惠卷列表
     * @param Request $request cartNumber
     * @return JsonResponse
     */
    public function listCanUsed(Request $request)
    {
        try {

            //取出會員資訊
            $memberID = $this->getMemberId();
            //取出購物車號碼
            $cartNumber = $request->query('cartNumber');
            //取出購物車號碼
            $prodId = $request->query('prodID');
            //跑api拿出購物車內的商品送去給CI專案吧
            $cartItems = $this->cartMoreService->mine(['cartNumber' => $cartNumber]);
            //拿出列表優惠倦
            $member_discounts = $this->service->listCanUsed($memberID);
            //判斷得程式
            $result = (new MemberDiscountResult)->listCanUsed($member_discounts, $cartItems, $memberID);
            //return $this->success($cartItems);

            return $this->success($result);


            //拿出 dicount_codes
        } catch (\Exception $e) {
            // Logger::error('v2/MemberDiscountController/list',$e->getMessage());
            return $this->failure('E0001', $e->getMessage());
        } //try

    } //end list



    /**
     *  //優惠卷列表
     * @param Request $request prodId
     * @return JsonResponse
     */
    public function listCanUsedByProdId(Request $request)
    {
        try {
            
            $prodId = $request->query('prodId');
            $action = $request->query('action');
            
            //取出會員資訊
            $memberID = $this->getMemberId();
            if (empty($memberID)) {
                $member_discounts = null;
            } else {
                //拿出列表優惠倦
                $member_discounts = $this->service->listCanUsed($memberID);
            }
            
            // 取購物車內容
            $cart = $this->cartService->find($action, $memberID);
            $cart = unserialize($cart);

            //判斷得程式
            $result = (new MemberDiscountResult)->listCanUsedByProd($member_discounts,$cart,$memberID);

            return $this->success($result);
        } catch (\Exception $e) {
            // Logger::error('v2/MemberDiscountController/list',$e->getMessage());
            return $this->failure('E0001', $e->getMessage());
        } //try

    } //end list

    /**
     *  商品可以領取的優惠券
     * @param Request $request 　prodId
     * @return JsonResponse
     */
    public function listByProd(Request $request, $prodId)
    {
        try {
            //目前系統上會同時有站方發的優惠券及店家發送的優惠券，由於是不同表不同邏輯，
            //故同一支API需拿兩邊的資料，各取出可用的優惠券，最後合併成一筆資料回給前端
            $memberID = $this->getMemberId();
            // ===== 站方優惠券開始 =====
            //拿出自己優惠倦
            if (empty($memberID)) {
                $member_discounts = null;
            } else {
                //拿出列表優惠倦
                $member_discounts = $this->service->listProdDiscount($memberID);
            }
            //拿出可以用的優惠倦
            $discountCodes = $this->discountCodeService->allEnableDiscountByProd($prodId);

            //判斷得程式
            $result = (new MemberDiscountResult)->listByProd($member_discounts, $discountCodes);


            // ===== 店家優惠券開始 =====
            $prod_data = $this->productService->findById($prodId);
            $diningCar_data = $this->diningCarService->findByName($prod_data->prod_store);
            $diningCar_id = $diningCar_data->id;
            $diningCar_name = $diningCar_data->name;

            //取出該店家所有目前可用的線上優惠券
            $coupon = $this->couponService->listCanUsedByDiningCarId($diningCar_id);
            //取出該會員目前已領的優惠券
            $member_coupon = $this->memberCouponService->list($memberID);
            //將優惠券資料轉成回給前端的格式
            $coupon_result = $this->memberCouponResult->listByProd($member_coupon,$coupon,$diningCar_name);

            //將兩種result合併
            foreach($coupon_result as $value){
                array_push($result,$value);
            }
            
            return $this->success($result);
        } catch (\Exception $e) {
            // Logger::error('v2/MemberDiscountController/list',$e->getMessage());
            return $this->failure('E0001', $e->getMessage());
        } //try

    } //end list


    /**
     *  //領取優惠卷
     * @param Request $request ['2019test','XXXXX'] 
     * @return JsonResponse
     */
    public function getByCode(Request $request)
    {
        try {
            $discountValueArray = $request->input('discountCode');
            if (empty($discountValueArray)) {
                return $this->failure('E0001', '沒有折價卷代碼');
            }
            $memberID = $this->getMemberId();

            foreach ($discountValueArray as $discountValue) {

                //拿出可以用的優惠倦
                $discountCodes = $this->discountCodeService->getEnableDiscountByCode($discountValue);
                $couponCodes = $this->couponService->getEnableCouponByCode($discountValue);

                //判斷是否有優惠倦 可是沒了 或者 代碼有錯誤
                if (empty($discountCodes)) {
                    if (empty($couponCodes)) {
                        $output = $this->failure('E0001', '領取完畢或代碼錯誤');
                    } else {
                        $result = $this->couponService->createAndCheck([
                            'member_id' =>  $memberID,
                            'coupon_id' => $couponCodes->id,                            
                            'is_collected' => 0,
                            'count' => 0
                        ]);
                        if ($result) {
                            $output = $this->success();
                        } else {
                            $output = $this->failure('E0001', '已經領取過');
                        }
                    }
                } else {

                    //寫入DB併檢查是否已經領取過
                    $result = $this->service->createAndCheck([
                        'discount_code_id' => $discountCodes->discount_code_id,
                        'member_id' =>  $memberID,
                        'used' => 0,
                        'status' => 1
                    ]);
                    if ($result) {
                        $output = $this->success();
                    } else {
                        $output = $this->failure('E0001', '已經領取過');
                    }
                }
            }
            if (count($discountValueArray) == 1) {
                return $output;
            } else {
                return $this->success();
            }
        } catch (\Exception $e) {
            // Logger::error('v2/MemberDiscountController/list',$e->getMessage());
            return $this->failure('E0001', $e->getMessage());
        } //try

    }

    /**
     *  //領取優惠卷
     * @param Request $request  current   used  disabled
     * @return JsonResponse
     */
    public function list(Request $request, $func)
    {
        $memberID = $this->getMemberId();

        switch ($func) {

            case 'current':
                //可使用 valid
                $data = $this->service->current($memberID);
                $dataCoupon = $this->couponService->memberCurrentCouponlist($memberID);
                break;
                //已經使用 used 
            case 'used':
                $data = $this->service->used($memberID);
                $dataCoupon = $this->couponService->memberUsedCouponlist($memberID);
                break;
                //以失效 disabled
            case 'disabled':
                $data = $this->service->disabled($memberID);
                $dataCoupon = $this->couponService->memberDisabledCouponlist($memberID);
                break;
            case 'all':
                $data1 = $this->service->current($memberID);
                $dataCoupon1 = $this->couponService->memberCurrentCouponlist($memberID);

                $data2 = $this->service->used($memberID);
                $dataCoupon2 = $this->couponService->memberUsedCouponlist($memberID);

                $data3 = $this->service->disabled($memberID);
                $dataCoupon3 = $this->couponService->memberDisabledCouponlist($memberID);

                $result1 = (new MemberDiscountResult)->list($data1, 'current');
                $resultCoupon1 = (new MemberDiscountResult)->memberCouponlist($dataCoupon1, 'current');
                $result2 = (new MemberDiscountResult)->list($data2, 'used');
                $resultCoupon2 = (new MemberDiscountResult)->memberCouponlist($dataCoupon2, 'used');
                $result3 = (new MemberDiscountResult)->list($data3, 'disabled');
                $resultCoupon3 = (new MemberDiscountResult)->memberCouponlist($dataCoupon3, 'disabled');
                $result = array_merge($result1, $resultCoupon1, $result2, $resultCoupon2, $result3, $resultCoupon3);
                return $this->success($result);
            default:
                return $this->failure('E0001', '錯誤代碼');
        } //end switch

        if (empty($data)) {
            return $this->success([]);
        } else {
            $result = (new MemberDiscountResult)->list($data, $func);
            $resultCoupon = (new MemberDiscountResult)->memberCouponlist($dataCoupon, $func);
            $result = array_merge($result, $resultCoupon);
            return $this->success($result);
        }
    } //end
}
