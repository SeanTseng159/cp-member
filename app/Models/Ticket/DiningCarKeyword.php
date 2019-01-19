<?php
/**
 * User: lee
 * Date: 2019/01/18
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class DiningCarKeyword extends BaseModel
{
	/**
     * 取得餐車
     */
  	public function diningCar()
    {
        return $this->hasOne('App\Models\Ticket\DiningCar', 'id', 'dining_car_id')->where('status', 1);
	}
}
