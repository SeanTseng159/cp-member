<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class OrderDetail extends BaseModel
{
	// protected $table = 'prods';
	protected $primaryKey = 'order_detail_id';

	/**
     * 取得訂單詳細
     */
  	public function productImg()
    {
        return $this->hasMany('App\Models\Ticket\ProductImg', 'prod_id', 'prod_id');
	}
}
