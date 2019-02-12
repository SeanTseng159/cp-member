<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class Keyword extends BaseModel
{
	protected $table = 'keywords';
    protected $primaryKey = 'keyword_id';

    /**
     * 取得商品所有關鍵字
     */
  	public function keywordProducts()
    {
        return $this->hasMany('App\Models\Ticket\ProductKeyword', 'keyword_id', 'keyword_id');
	}

	/**
     * 依關鍵字取得所有餐車
     */
  	public function diningCars()
    {
        return $this->hasMany('App\Models\Ticket\DiningCarKeyword', 'keyword_id', 'keyword_id');
	}
}
