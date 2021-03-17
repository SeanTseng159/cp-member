<?php


namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class MemberCouponOnline extends BaseModel
{

    
    protected $table = 'member_coupon_online';
    protected $fillable = ['used_time','update_at'];

     /**
     * 由於在判斷有哪些member_coupon_online內有哪些優惠券可用或不可用時，需要使用到coupons的資料表，判斷是否到期等等
     */
    public function coupon()
    {
        return $this->hasOne('App\Models\Ticket\Coupon', 'coupons_id' , 'id');
    }

    /*public function discountCode()
    {
        return $this->hasOne('App\Models\Ticket\Coupon', 'discount_code_id', 'discount_code_id');
    }*/
    
    
}
 