<?php
/**
 * User: lee
 * Date: 2018/12/14
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class Promotion extends BaseModel
{
	/**
     * 取得優惠條件
     */
  	public function conditions()
    {
        return $this->hasMany('App\Models\Ticket\PromotionCondition')->orderBy('condition', 'asc');
	}

	/**
     * 取得banner
     */
  	public function banner()
    {
        return $this->hasOne('App\Models\Ticket\PromotionImg');
	}

	/**
     * 取得商品
     */
  	public function prodSpecPrices()
    {
        return $this->hasMany('App\Models\Ticket\PromotionProdSpecPrice')->orderBy('sort', 'asc');
	}

    /**
     * 取得運費
     */
    public function shipping()
    {
        return $this->hasMany('App\Models\Ticket\PromotionShipping')->orderBy('lower', 'asc');
    }
}
