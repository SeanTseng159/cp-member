<?php
/**
 * User: Annie
 * Date: 2019/02/14
 * Time: 上午 11:55
 */

namespace App\Result\Ticket;

use App\Helpers\CommonHelper;
use App\Result\BaseResult;
use Carbon\Carbon;
use App\Traits\StringHelper;
use App\Helpers\ImageHelper;

class CouponResult extends BaseResult
{
    use StringHelper;

    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * 取動態消息列表
     *
     * @param $coupons
     * @param $memberCoupons
     *
     * @return array
     */
    public function list($coupons,$memberCoupons)
    {
        $resultAry = [];
        
       
        foreach ($coupons as $coupon)
        {
            $result = new \stdClass;
            $result->id = $coupon->couponID;
            $result->Name = $coupon->name;
            $result->title = $coupon->couponTitle;
            $result->content = $coupon->couponContent;
            $result->duration = $coupon->duration;
            $result->favorite = false;
            $result->used = false;
            $result->allused = false;
            
           
            
            if ((int)$coupon->totalUsedCount >= $coupon->CouponQty )
            {
                $result->allused = true;
            }
        
        
            if ($memberCoupons->isNotEmpty())
            {
                $memberCoupon = $memberCoupons->where('coupon_id', $coupon->couponID)->first();
            
                if ($memberCoupon)
                {
                    $couponLimit = $coupon->couponLimitQty;
                    
                    //已經使用完個人的限制張數
                    if ($memberCoupon->count >= $couponLimit)
                    {
                        $result->used = true;
                    }
                    if ($memberCoupon->is_collected)
                    {
                    
                        $result->favorite = true;
                    }
                
                }
            }
            $resultAry[] = $result;
        }
        return $resultAry;
    }
    
    /**
     * 優惠卷資訊
     *
     * @param      $coupon
     * @param      $memberCoupon
     *
     * @param      $images
     *
     * @return \stdClass|null
     */
    public function detail($coupon, $memberCoupon, $images){
        
        if (!$coupon) return null;

        $result = new \stdClass;
        $result->title = $coupon->couponTitle;
        $result->content = $coupon->couponContent;
        $result->duration= $coupon->duration;
        $result->desc= $coupon->couponDesc;
        $result->favorite= false;
        $result->status = 0 ; //0:未使用 1:已使用 2:優惠券已兌換完畢 3.優惠券已失效(過期)
        $result->shareUrl = CommonHelper::getWebHost('zh-TW/diningCar/detail/' . $coupon->couponId);
        $result->photo = $images;
    
        $startAt = Carbon::createFromFormat('Y-m-d i:s:u',$coupon->couponStartAt);
        $expiredAt = Carbon::createFromFormat('Y-m-d i:s:u',$coupon->couponExpireAt);
    
        
        
        //優惠卷狀態
        if(!Carbon::now()->between($startAt, $expiredAt))
            $result->status = 3 ; //已失效
       
        $couponLimit = $coupon->couponLimitQty;
    
        if ($memberCoupon)
        {
            if ($memberCoupon->count > 0 && $memberCoupon->count < $couponLimit)
            {
                $result->status = 1; // 已使用
            }
            if ($memberCoupon->count >= $couponLimit)
            {
                $result->status = 2; // 已兌換完畢
            }
        
            if ($memberCoupon->is_collected)
            {
                $result->favorite = true;
            }
        }
        return $result;
    }
    
    
    
}
