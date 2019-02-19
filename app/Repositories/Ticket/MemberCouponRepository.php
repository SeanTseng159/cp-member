<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Models\Ticket\MemberCoupon;
use App\Repositories\BaseRepository;
use Carbon\Carbon;

class MemberCouponRepository extends BaseRepository
{
    private $limit = 20;
    
    public function __construct(MemberCoupon $model)
    {
        $this->model = $model;
    }
    
    /** 取得使用者之優惠劵列表，若$couponId 有值，則取得該coupon資料
     * @param      $memberID
     * @param null $couponID
     *
     * @return mixed
     */
    public function list($memberID,$couponID = null)
    {
        
        
        return $this->model
            ->select('coupon_id','is_collected','count')
            ->where('member_id', $memberID)
            ->when($couponID, function ($query) use ($couponID) {
                $query->where('coupon_id',$couponID);
            })
            ->get();
    }
    
    /** 取得使用者之優惠劵列表與優惠卷詳細資訊
     *
     * @param      $memberID
     *
     * @param      $status
     *                      current 未使用 1
     *                      used    已使用 2
     *                      expired 已失效 3
 *
     * @return mixed
     */
    public function favoriteCouponList($memberID,$status)
    {
        
    
        //取得該餐車所有的優惠卷
        $result = $this->model
            ->join('coupons', 'coupons.id', '=', "coupon_id")
            ->select(
                'name',
                'start_at',
                'expire_at',
                'limit_qty',
                'content',
                'desc'
            )
            ->where('member_id',$memberID)
            ->where('status',1)
            ->where('is_collected',1)
            ->when($status,
                function ($query) use ( $status) {
                    if ($status === 1)
                    {
                        $query->where('count', 0)
                            ->where('coupons.start_at', '<=', Carbon::now()->toDateTimeString())
                            ->where('coupons.expire_at', '>=', Carbon::now()->toDateTimeString());
                    }
                    elseif ($status === 2)
                    {
                        $query->where('count', '>', 0)
                            ->where('coupons.start_at', '<=', Carbon::now()->toDateTimeString())
                            ->where('coupons.expire_at', '>=', Carbon::now()->toDateTimeString());
                    }
                    elseif ($status === 3)
                    {
                        $query->where('coupons.expire_at', '<=', Carbon::now()->toDateTimeString());
                    }
            
                })
            ->orderBy('expire_at','asc')
            ->orderBy('member_coupon.updated_at','asc')
            ->get();
        
        
        return $result;
    }



    
    /**
     *  優惠卷加入使用者的收藏
     *
     * @param $memberId
     * @param $couponID
     *
     * @return MemberCoupon
     */
    
    public function add($memberId,$couponID)
    {
        $model = new MemberCoupon();
        
        $model->member_id = $memberId;
        $model->coupon_id = $couponID;
        $model->count = 0 ;
        $model->is_collected = 1 ;
        $model->save();
    
        return $model;
    
    }
    
    
    /**
     *  優惠卷從使用者的收藏移除
     *
     * @param $memberId
     * @param $couponID
     *
     * @param $isFavorite
     *
     * @return MemberCoupon
     */
    
    public function update($memberId,$couponID,$isFavorite)
    {
        return $this->model
            ->where('member_id', $memberId)
            ->where('coupon_id', $couponID)
            ->update(['is_collected' => $isFavorite]);
        
    }
    
    
    
    
    
    
}
