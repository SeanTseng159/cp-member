<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\DiscountCode;
use App\Models\OrderDiscount;
use App\Models\Ticket\BaseModel;

class Order extends BaseModel
{
	// protected $table = 'prods';
	protected $primaryKey = 'order_id';

	public $timestamps = false;

	/**
     * 取得訂單詳細
     */
  	public function detail()
    {
        return $this->hasMany('App\Models\Ticket\OrderDetail', 'order_no', 'order_no');
	}

	/**
     * 取得訂單詳細
     */
  	public function details()
    {
        return $this->hasMany('App\Models\Ticket\OrderDetail', 'order_no', 'order_no')->where('prod_type', '!=', 4);
	}

    /**
     * 取得訂單詳細
     */
    public function shipment()
    {
        return $this->hasOne('App\Models\Ticket\OrderShipment', 'order_id', 'order_id');
    }

    /**
     * 取得訪客訂單相關資訊
     */
    public function guestOrder()
    {
        return $this->hasOne('App\Models\Ticket\GuestOrder', 'order_id', 'order_id');
    }

    /*
     * 優惠代碼資訊
     */
    public function discountCode(){
        return $this->hasOne(OrderDiscount::class, 'order_no', 'order_no');
    }
}
