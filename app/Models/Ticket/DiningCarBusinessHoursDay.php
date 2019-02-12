<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class DiningCarBusinessHoursDay extends BaseModel
{
	/**
     * 取得營業時間
     */
    public function times()
    {
        return $this->hasMany('App\Models\Ticket\DiningCarBusinessHoursTime')->where('status', 1)->orderBy('sort', 'asc');
    }
}
