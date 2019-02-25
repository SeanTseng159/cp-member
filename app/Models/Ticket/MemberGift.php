<?php
/**
 * User: Annie
 * Date: 2019/02/22
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

class MemberGift extends BaseModel
{
    protected $table = 'member_gifts';

    public function __construct(){
    
    }
    
    
    public function giftItems()
    {
        return $this->hasMany('App\Models\Ticket\MemberGiftItem','member_gift_id','id');
    }

}
