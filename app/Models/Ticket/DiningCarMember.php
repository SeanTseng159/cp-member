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

	/**
     * 取得餐車
     */
    public function diningCar()
    {
        return $this->belongsTo('App\Models\Ticket\DiningCar');
    }

    /**
     * 取得禮物
     */
    public function gifts()
    {
        return $this->hasMany('App\Models\Ticket\Gift', 'model_spec_id', 'dining_car_id')->where('model_type', 'dining_car');
    }
}
