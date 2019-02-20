<?php

namespace App\Http\Controllers\Api\V1;


use App\Services\Ticket\MemberCouponItemService;
use App\Services\Ticket\MemberCouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Ksd\Mediation\Core\Controller\RestLaravelController;



class MemberCouponController extends RestLaravelController
{
    protected $lang = 'zh-TW';
    protected $service;
    protected $memberCouponService;
    protected $memberCouponItemService;
    
    
    public function __construct(MemberCouponService $memberCouponService,MemberCouponItemService $memberCouponItemServic)
    {
        $this->memberCouponService = $memberCouponService;
        $this->memberCouponItemService = $memberCouponItemServic;
    }
    
    
    /**
     * 新增coupon收藏
     *
     * @param Request $request
     * @param         $couponId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addFavorite(Request $request,$couponId)
    {
        try
        {
            
            $memberId = $request->memberId;
            $memberCoupon = $this->memberCouponService->find($memberId, $couponId);
            
            
            //不在收藏列表內，新增一筆資料，否則更新收藏狀態
            if (!$memberCoupon)
            {
                $result = $this->memberCouponService->add($memberId, $couponId);
                
            }
            else if (!$memberCoupon->is_collected)
            {
                $result = $this->memberCouponService->update($memberId, $couponId, true);
                
            }
            else if ($memberCoupon->is_collected)
            {
                $result = true;
            }

            

            return ($result) ? $this->success() : $this->failureCode('E0040');
        }
        catch (\Exception $e)
        {
            return $this->failureCode('E0040');
        }
        
        
        
        
    }
    
    /**
     * coupon 移除收藏
     *
     * @param Request $request
     * @param         $couponId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFavorite(Request $request,$couponId)
    {
        
        try
        {
            $memberId = $request->memberId;
    
            $result = $this->memberCouponService->update($memberId, $couponId, false);
            
            return ($result) ? $this->success() : $this->failureCode('E0041');
        }
        catch (\Exception $e)
        {
            return $this->failureCode('E0041');
        }
        
    }
    
    /**
     * 取得我的coupon可使用清單
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function list(Request $request){
    
        //try
        {
            $memberId = $request->memberId;
            $listType = Input::get('type', 'current');
            //current 未使用 1
            //used    已使用 2
            //expired 已失效 3
            $allowStatus = ['current' => 1, 'used' => 2, 'expired' => 3];
            if (!array_key_exists($listType,$allowStatus))
            {
                throw  new \Exception('E0001');
            }
            $status = $allowStatus[$listType];
            
            
            //取得使用者所有coupon
            $memberCoupons = $this->memberCouponService->favoriteCouponList($memberId,$status);
            
            return $this->success($memberCoupons);
        }
//        catch (\Exception $e)
//        {
//            return $this->failureCode('E0001');
//        }
    
        
    }
    

}
