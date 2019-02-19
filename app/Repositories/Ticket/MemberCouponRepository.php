<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Models\Ticket\MemberCoupon;
use App\Repositories\BaseRepository;

class MemberCouponRepository extends BaseRepository
{
    private $limit = 20;
    
    public function __construct(MemberCoupon $model)
    {
        $this->model = $model;
    }
    
    /**
     * @param      $params
     * @param null $couponID
     *
     * @return mixed
     */
    public function list($params,$couponID = null)
    {
        
        $memberId = $params->memberId ;
//        $memberId = 1 ;
        return $this->model
            ->select('coupon_id','is_collected','count')
            ->where('member_id', $memberId)
            ->when($couponID, function ($query) use ($couponID) {
                $query->where('coupon_id',$couponID);
            })
            ->get();
    }
    
    
    
}
