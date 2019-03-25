<?php
/**
 * User: Annie
 * Date: 2019/02/22
 * Time: 上午 10:03
 */

namespace App\Models;

use App\Models\Ticket\BaseModel;
use App\Models\Ticket\DiningCar;

class MemberGiftItem extends BaseModel
{
    protected $table = 'member_gift_items';
    protected $connection = 'backend';
    protected $fillable = ['member_id', 'gift_id', 'number', 'used_time'];


    public function __construct()
    {

    }

    public function gift()
    {
        return $this->hasOne(Gift::class,'id','gift_id');
    }


    public function member()
    {
        return $this->belongsTo(Member::class, 'id', 'member_id');
    }


    public function scopeByUser($query, $memberId)
    {
        return $query->where('member_id', $memberId);
    }


}
