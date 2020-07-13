<?php
/**
 * User: lee
 * Date: 2020/07/12
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;


class GuestOrder extends BaseModel
{
    protected $connection = 'backend';

    /**
     * 取得訂單
     */
  	public function order()
    {
        return $this->hasOne('App\Models\Ticket\Order', 'order_id', 'order_id');
	}
}
