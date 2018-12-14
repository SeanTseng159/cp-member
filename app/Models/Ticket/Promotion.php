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
  	public function condition()
    {
        return $this->hasOne('App\Models\Ticket\PromotionCondition');
	}

	/**
     * 取得優banner
     */
  	public function banner()
    {
        return $this->hasOne('App\Models\Ticket\PromotionImg');
	}

	/**
     * 取得優banner
     */
  	public function prodSpecPrices()
    {
        return $this->hasMany('App\Models\Ticket\PromotionProdSpecPrice')->orderBy('sort', 'asc');
	}
}
