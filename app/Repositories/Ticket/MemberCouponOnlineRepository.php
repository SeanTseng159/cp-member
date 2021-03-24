<?php

/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;




use App\Repositories\BaseRepository;

use Carbon\Carbon;
use DB;
use App\Core\Logger;
use App\Models\Coupon;
use App\Models\Ticket\MemberCouponOnline;
use Log;

class MemberCouponOnlineRepository extends BaseRepository
{
    private $model;

    public function __construct(MemberCouponOnline $model)
    {
        $this->model = $model;
    }

    //搜尋"member已經領過的優惠券，且優惠券目前在可以使用的狀態"
    public function listCanUsed($memberID)
    {
        $couponCanUsed = $this->model
        ->join('coupons', 'member_coupon.coupon_id' , '=' , 'coupons.id')//把coupons表join進來，查看member擁有的coupon內，有哪些符合資格
        ->select('member_coupon.*','coupons.*')
        ->where('coupons.start_at' , '<=' , Carbon::now())//現在日期必須包含在優惠券的使用時間內
        ->where('coupons.expire_at', '>' , Carbon::now())
        ->where('coupons.status' , '1') //優惠券必須在可以使用的狀態
        ->where('coupons.online_or_offline', '2')//以防萬一，多判斷只取線上優惠券
        ->get();
        
        return $couponCanUsed;
        
    }


}
