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

	public $timestamps = false;

	/**
     * 依據商品ID取得商品圖片
     */
  	public function productImg()
    {
        return $this->hasMany('App\Models\Ticket\ProductImg', 'prod_id', 'prod_id');
	}

	/**
     * 依據規格票種ID取得資料
     */
  	public function productSpecPrice()
    {
        return $this->hasOne('App\Models\Ticket\ProductSpecPrice', 'prod_spec_price_id', 'prod_spec_price_id');
	}
}
