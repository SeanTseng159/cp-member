<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class LayoutCategory extends BaseModel
{
	protected $primaryKey = 'layout_category_id';

	/**
     * 取得商品
     */
  	public function products()
    {
        return $this->hasMany('App\Models\Ticket\LayoutCategoryProduct', 'layout_category_id');
	}
}
