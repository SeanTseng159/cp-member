<?php
/**
 * User: lee
 * Date: 2019/01/08
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class MemberDiningCar extends BaseModel
{
	/**
     * 取得社群網址
     */
    public function diningCar()
    {
        return $this->hasOne('App\Models\Ticket\DiningCar', 'id', 'dining_car_id')->where('status', 1);
    }
}
