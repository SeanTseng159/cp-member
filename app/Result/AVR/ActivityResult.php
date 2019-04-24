<?php
/**
 * User: Annie
 * Date: 2019/02/14
 * Time: 上午 11:55
 */

namespace App\Result\AVR;

use App\AVR\Helpers\AVRImageHelper;
use App\Enum\AVRClientType;
use App\Enum\ClientType;
use App\Helpers\CommonHelper;
use App\Result\BaseResult;
use Carbon\Carbon;
use App\Traits\StringHelper;
use App\Helpers\ImageHelper;

class ActivityResult extends BaseResult
{
    use StringHelper;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     *
     * @param $activities
     * @return array
     */
    public function list($activities)
    {
        $resultAry = [];


        foreach ($activities as $activity) {
            $result = new \stdClass;
            $result->id = $activity->id;
            $result->name = $activity->name;
            $result->duration = Carbon::parse($activity->start_activity_time)->format('Y-m-d') .
                "~" .
                Carbon::parse($activity->end_activity_time)->format('Y-m-d');

            //圖片
            $result->photo = AVRImageHelper::getImageUrl(AVRClientType::activity, $activity->id);

            $resultAry[] = $result;
        }
        return $resultAry;
    }

//    /**
//     * 優惠卷資訊
//     *
//     * @param      $coupon
//     * @param      $memberCoupon
//     *
//     * @param      $images
//     *
//     * @return \stdClass|null
//     */
//    public function detail($coupon, $memberCoupon, $images)
//    {
//
//        if (!$coupon) return null;
//
//        $result = new \stdClass;
//        $result->title = $coupon->couponTitle;
//        $result->content = $coupon->couponContent;
//        $result->duration = $coupon->duration;
//        $result->desc = $coupon->couponDesc;
//        $result->favorite = false;
//        $result->status = 0; //0:未使用 1:已使用 2:優惠券已兌換完畢 3.優惠券已失效(過期)
//        $result->shareUrl = CommonHelper::getWebHost('zh-TW/diningCar/detail/' . $coupon->couponId);
//        $result->photo = $images;
//
//        $startAt = Carbon::createFromFormat('Y-m-d i:s:u', $coupon->couponStartAt);
//        $expiredAt = Carbon::createFromFormat('Y-m-d i:s:u', $coupon->couponExpireAt);
//
//        //優惠卷狀態
//        if (!Carbon::now()->between($startAt, $expiredAt))
//            $result->status = 3; //已失效
//
//
//        $couponLimit = $coupon->couponLimitQty;
//        $couponQty = $coupon->CouponQty;
//
//        if ($memberCoupon) {
//            if ($couponQty <= 0) {
//                $result->status = 2; // 所有已兌換完畢
//            }
//
//            if ($memberCoupon->count >= $couponLimit) {
//                $result->status = 1; // 已使用完個人限制
//            }
//
//
//            if ($memberCoupon->is_collected) {
//                $result->favorite = true;
//            }
//        }
//
//        return $result;
//    }


}
