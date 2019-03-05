<?php
/**
 * User: lee
 * Date: 2019/02/19
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiningCarMember extends BaseModel
{
	use SoftDeletes;

	protected $guarded = ['id'];
	protected $appends = ['gift_Count'];

	/**
     * 取得餐車
     */
    public function diningCar()
    {
        return $this->belongsTo('App\Models\Ticket\DiningCar');
    }

    /**
     * 取得領取禮物列表
     */
    public function getGiftCountAttribute()
    {
        return $this->hasMany('App\Models\Ticket\MemberGiftItem', 'member_id', 'member_id')->count();
    }
}
